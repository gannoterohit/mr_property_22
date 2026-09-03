<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Blog;
use App\Models\City;
use App\Models\CityAlert;
use App\Models\Complaint;
use App\Models\ContactMessage;
use App\Models\HomeFeature;
use App\Models\HowItWorksItem;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\RejectionReason;
use App\Models\Room;
use App\Models\RoomOption;
use App\Models\SearchLog;
use App\Models\Subscriber;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataTransferController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    public function export(string $dataset)
    {
        abort_unless(array_key_exists($dataset, $this->exportCatalog()), 404);

        [$headers, $rows] = $this->datasetRows($dataset);
        $filename = $dataset.'-'.now()->format('Y-m-d-His').'.xls';

        return response($this->excelHtml($headers, $rows, $this->exportCatalog()[$dataset]['label']), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function template(string $dataset)
    {
        abort_unless(array_key_exists($dataset, $this->importCatalog()), 404);

        $headers = $this->importCatalog()[$dataset]['columns'];

        return response($this->excelHtml($headers, collect([$this->sampleRow($headers)]), $this->importCatalog()[$dataset]['label'].' Template'), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$dataset.'-template.xls"',
        ]);
    }

    public function import(Request $request, string $dataset)
    {
        abort_unless(array_key_exists($dataset, $this->importCatalog()), 404);

        $request->validate([
            'file' => ['required', 'file', 'extensions:csv,txt,xls,xlsx', 'max:5120'],
        ]);

        [$created, $updated, $skipped, $errors] = [0, 0, 0, []];
        $uploadedFile = $request->file('file');
        $importRows = $this->readImportFile($uploadedFile->getRealPath(), $uploadedFile->getClientOriginalExtension());
        $headers = array_shift($importRows) ?: [];
        $headers = array_map(fn ($value) => Str::slug(trim((string) $value), '_'), $headers);

        DB::beginTransaction();
        try {
            $line = 1;
            foreach ($importRows as $values) {
                $line++;
                $row = array_combine($headers, array_pad($values, count($headers), null)) ?: [];
                $row = collect($row)->map(fn ($value) => is_string($value) ? trim($value) : $value)->all();

                try {
                    $result = $this->importRow($dataset, $row);
                    $created += $result === 'created' ? 1 : 0;
                    $updated += $result === 'updated' ? 1 : 0;
                    $skipped += $result === 'skipped' ? 1 : 0;
                } catch (\Throwable $exception) {
                    $skipped++;
                    $errors[] = "Line {$line}: ".$exception->getMessage();
                }
            }
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Import failed: '.$exception->getMessage()]);
        }

        return back()->with('success', "Import completed. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.")
            ->with('import_errors', array_slice($errors, 0, 10));
    }

    public function report(string $dataset)
    {
        abort_unless(array_key_exists($dataset, $this->reportCatalog()), 404);

        [$columns, $rows] = $this->datasetRows($dataset);

        return view('admin.data-transfer.report', [
            'title' => $this->reportCatalog()[$dataset]['label'],
            'dataset' => $dataset,
            'columns' => $columns,
            'rows' => $rows,
            'generatedAt' => now(),
        ]);
    }

    private function datasetRows(string $dataset): array
    {
        return match ($dataset) {
            'property-types' => [
                ['id', 'name', 'slug', 'status', 'created_at'],
                PropertyType::orderBy('name')->get()->map(fn ($item) => [
                    $item->id, $item->name, $item->slug, $item->status ? 1 : 0, $item->created_at,
                ]),
            ],
            'property-categories' => [
                ['id', 'property_type', 'name', 'slug', 'status', 'created_at'],
                PropertyCategory::with('propertyType')->orderBy('name')->get()->map(fn ($item) => [
                    $item->id, $item->propertyType?->name, $item->name, $item->slug, $item->status ? 1 : 0, $item->created_at,
                ]),
            ],
            'room-options' => [
                ['id', 'group', 'key', 'label', 'sort_order', 'is_active'],
                RoomOption::orderBy('group')->orderBy('sort_order')->get()->map(fn ($item) => [
                    $item->id, $item->group, $item->key, $item->label, $item->sort_order, $item->is_active ? 1 : 0,
                ]),
            ],
            'cities' => [
                ['id', 'name', 'slug', 'state', 'is_active', 'is_default', 'latitude', 'longitude', 'sort_order'],
                City::orderBy('sort_order')->orderBy('name')->get()->map(fn ($item) => [
                    $item->id, $item->name, $item->slug, $item->state, $item->is_active ? 1 : 0, $item->is_default ? 1 : 0, $item->latitude, $item->longitude, $item->sort_order,
                ]),
            ],
            'rejection-reasons' => [
                ['id', 'reason', 'is_active'],
                RejectionReason::orderBy('reason')->get()->map(fn ($item) => [
                    $item->id, $item->reason, $item->is_active ? 1 : 0,
                ]),
            ],
            'home-features' => [
                ['id', 'title', 'description', 'icon', 'sort_order', 'is_active'],
                HomeFeature::orderBy('sort_order')->get()->map(fn ($item) => [
                    $item->id, $item->title, $item->description, $item->icon, $item->sort_order, $item->is_active ? 1 : 0,
                ]),
            ],
            'how-it-works' => [
                ['id', 'group', 'title', 'description', 'icon', 'badge', 'sort_order', 'is_active'],
                HowItWorksItem::orderBy('group')->orderBy('sort_order')->get()->map(fn ($item) => [
                    $item->id, $item->group, $item->title, $item->description, $item->icon, $item->badge, $item->sort_order, $item->is_active ? 1 : 0,
                ]),
            ],
            'testimonials' => [
                ['id', 'name', 'role', 'city', 'message', 'rating', 'status', 'sort_order'],
                Testimonial::orderBy('sort_order')->get()->map(fn ($item) => [
                    $item->id, $item->name, $item->role, $item->city, $item->message, $item->rating, $item->status, $item->sort_order,
                ]),
            ],
            'rooms' => [
                ['id', 'title', 'owner', 'owner_phone', 'property_type', 'category', 'room_option', 'city', 'rent', 'deposit', 'area_sqft', 'status', 'listing_status', 'listing_fee_paid', 'photos_count', 'created_at'],
                Room::with(['owner', 'propertyType', 'propertyCategory', 'roomTypeOption'])->latest()->get()->map(fn ($room) => [
                    $room->id, $room->title, $room->owner?->name, $room->owner?->phone, $room->propertyType?->name, $room->propertyCategory?->name, $room->roomTypeOption?->label, $room->city, $room->rent, $room->deposit, $room->area_sqft, $room->status, $room->listing_status, $room->listing_fee_paid ? 1 : 0, count($room->photos ?: []), $room->created_at,
                ]),
            ],
            'users' => $this->memberRows('user'),
            'owners' => $this->memberRows('owner'),
            'staff' => $this->memberRows('admin'),
            'payments' => [
                ['id', 'user', 'type', 'amount', 'gateway', 'transaction_id', 'reference_id', 'status', 'created_at'],
                Payment::with('user')->latest()->get()->map(fn ($payment) => [
                    $payment->id, $payment->user?->name, $payment->type, $payment->amount, $payment->gateway, $payment->transaction_id, $payment->reference_id, $payment->status, $payment->created_at,
                ]),
            ],
            'plans' => [
                ['id', 'name', 'type', 'price', 'duration_days', 'listing_limit', 'contacts_limit', 'is_active', 'created_at'],
                Plan::orderBy('type')->orderBy('price')->get()->map(fn ($plan) => [
                    $plan->id, $plan->name, $plan->type, $plan->price, $plan->duration_days, $plan->listing_limit, $plan->contacts_limit, $plan->is_active ? 1 : 0, $plan->created_at,
                ]),
            ],
            'offers' => [
                ['id', 'title', 'placement', 'type', 'discount_text', 'target_audience', 'is_active', 'start_date', 'end_date', 'created_at'],
                Offer::latest()->get()->map(fn ($offer) => [
                    $offer->id, $offer->title, $offer->placement, $offer->type, $offer->discount_text, $offer->target_audience, $offer->is_active ? 1 : 0, $offer->start_date, $offer->end_date, $offer->created_at,
                ]),
            ],
            'blogs' => [
                ['id', 'title', 'slug', 'is_published', 'meta_title', 'created_at'],
                Blog::latest()->get()->map(fn ($blog) => [
                    $blog->id, $blog->title, $blog->slug, $blog->is_published ? 1 : 0, $blog->meta_title, $blog->created_at,
                ]),
            ],
            'subscribers' => [
                ['id', 'email', 'created_at'],
                Subscriber::latest()->get()->map(fn ($subscriber) => [
                    $subscriber->id, $subscriber->email, $subscriber->created_at,
                ]),
            ],
            'contact-messages' => [
                ['id', 'name', 'email', 'subject', 'is_read', 'ip_address', 'created_at'],
                ContactMessage::latest()->get()->map(fn ($message) => [
                    $message->id, $message->name, $message->email, $message->subject, $message->is_read ? 1 : 0, $message->ip_address, $message->created_at,
                ]),
            ],
            'city-alerts' => [
                ['id', 'user', 'email', 'city', 'created_at'],
                CityAlert::with('user:id,name,email')->latest()->get()->map(fn ($alert) => [
                    $alert->id, $alert->user?->name, $alert->user?->email, $alert->city, $alert->created_at,
                ]),
            ],
            'complaints' => [
                ['id', 'ticket_number', 'user', 'room', 'category', 'priority', 'status', 'assigned_to', 'due_at', 'created_at'],
                Complaint::with(['user:id,name', 'room:id,title', 'assignee:id,name'])->latest()->get()->map(fn ($complaint) => [
                    $complaint->id, $complaint->ticket_number, $complaint->user?->name, $complaint->room?->title, $complaint->category, $complaint->priority, $complaint->status, $complaint->assignee?->name, $complaint->due_at, $complaint->created_at,
                ]),
            ],
            'search-logs' => [
                ['id', 'search_term', 'city', 'filters', 'user_id', 'ip_address', 'created_at'],
                SearchLog::latest()->limit(5000)->get()->map(fn ($log) => [
                    $log->id, $log->search_term, $log->city, $log->filters ?: [], $log->user_id, $log->ip_address, $log->created_at,
                ]),
            ],
            'activity-logs' => [
                ['id', 'actor', 'method', 'route_name', 'description', 'ip_address', 'created_at'],
                AdminActivityLog::with('actor:id,name')->latest()->limit(5000)->get()->map(fn ($log) => [
                    $log->id, $log->actor?->name, $log->method, $log->route_name, $log->description, $log->ip_address, $log->created_at,
                ]),
            ],
            default => abort(404),
        };
    }

    private function memberRows(string $role): array
    {
        return [
            ['id', 'name', 'email', 'phone', 'role', 'is_verified', 'is_blocked', 'verification_status', 'rooms_count', 'wallet_balance', 'created_at'],
            User::withCount('rooms')->where('role', $role)->latest()->get()->map(fn ($user) => [
                $user->id, $user->name, $user->email, $user->phone, $user->role, $user->is_verified ? 1 : 0, $user->is_blocked ? 1 : 0, $user->verification_status, $user->rooms_count, $user->wallet_balance, $user->created_at,
            ]),
        ];
    }

    private function importRow(string $dataset, array $row): string
    {
        return match ($dataset) {
            'property-types' => $this->upsert(PropertyType::class, ['slug' => $this->slug($row['slug'] ?? $row['name'] ?? '')], [
                'name' => $this->required($row, 'name'),
                'slug' => $this->slug($row['slug'] ?? $row['name']),
                'status' => $this->bool($row['status'] ?? true),
            ]),
            'property-categories' => $this->upsert(PropertyCategory::class, ['slug' => $this->slug($row['slug'] ?? $row['name'] ?? '')], [
                'property_type_id' => $this->propertyTypeId($row['property_type'] ?? $row['property_type_id'] ?? null),
                'name' => $this->required($row, 'name'),
                'slug' => $this->slug($row['slug'] ?? $row['name']),
                'status' => $this->bool($row['status'] ?? true),
            ]),
            'room-options' => $this->upsert(RoomOption::class, ['group' => $this->required($row, 'group'), 'key' => $this->slug($row['key'] ?? $row['label'] ?? '')], [
                'group' => $this->required($row, 'group'),
                'key' => $this->slug($row['key'] ?? $row['label']),
                'label' => $this->required($row, 'label'),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => $this->bool($row['is_active'] ?? true),
            ]),
            'cities' => $this->upsert(City::class, ['slug' => $this->slug($row['slug'] ?? $row['name'] ?? '')], [
                'name' => $this->required($row, 'name'),
                'slug' => $this->slug($row['slug'] ?? $row['name']),
                'state' => $row['state'] ?? null,
                'is_active' => $this->bool($row['is_active'] ?? true),
                'is_default' => $this->bool($row['is_default'] ?? false),
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ]),
            'rejection-reasons' => $this->upsert(RejectionReason::class, ['reason' => $this->required($row, 'reason')], [
                'reason' => $this->required($row, 'reason'),
                'is_active' => $this->bool($row['is_active'] ?? true),
            ]),
            'home-features' => $this->upsert(HomeFeature::class, ['title' => $this->required($row, 'title')], [
                'title' => $this->required($row, 'title'),
                'description' => $row['description'] ?? null,
                'icon' => $row['icon'] ?? 'fa-circle-check',
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => $this->bool($row['is_active'] ?? true),
            ]),
            'how-it-works' => $this->upsert(HowItWorksItem::class, ['group' => $this->required($row, 'group'), 'title' => $this->required($row, 'title')], [
                'group' => $this->required($row, 'group'),
                'title' => $this->required($row, 'title'),
                'description' => $row['description'] ?? null,
                'icon' => $row['icon'] ?? 'fa-circle-check',
                'badge' => $row['badge'] ?? null,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_active' => $this->bool($row['is_active'] ?? true),
            ]),
            'testimonials' => $this->upsert(Testimonial::class, ['name' => $this->required($row, 'name'), 'message' => $this->required($row, 'message')], [
                'name' => $this->required($row, 'name'),
                'role' => $row['role'] ?? null,
                'city' => $row['city'] ?? null,
                'message' => $this->required($row, 'message'),
                'rating' => max(1, min(5, (int) ($row['rating'] ?? 5))),
                'status' => in_array(($row['status'] ?? 'active'), ['active', 'inactive'], true) ? $row['status'] : 'active',
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ]),
            default => 'skipped',
        };
    }

    private function upsert(string $model, array $where, array $data): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $record */
        $record = $model::query()->where($where)->first();
        if ($record) {
            $record->update($data);
            return 'updated';
        }

        $model::create($data);
        return 'created';
    }

    private function readImportFile(string $path, string $extension): array
    {
        if (Str::lower($extension) === 'xlsx') {
            return $this->readXlsx($path);
        }
        if (Str::lower($extension) === 'xls') {
            return $this->readHtmlTable($path);
        }

        $rows = [];
        $handle = fopen($path, 'r');
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readHtmlTable(string $path): array
    {
        $html = file_get_contents($path);
        if ($html === false || trim($html) === '') {
            return [];
        }

        $rows = [];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        foreach ($dom->getElementsByTagName('tr') as $tr) {
            $row = [];
            foreach ($tr->childNodes as $cell) {
                if (in_array($cell->nodeName, ['th', 'td'], true)) {
                    $row[] = trim($cell->textContent);
                }
            }
            if (array_filter($row, fn ($value) => trim((string) $value) !== '')) {
                $rows[] = $row;
            }
        }
        libxml_clear_errors();

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('XLSX import requires PHP Zip extension. Please enable zip in XAMPP or upload CSV.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Unable to open XLSX file.');
        }

        $sharedStrings = [];
        if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== false) {
            $shared = simplexml_load_string($xml);
            foreach ($shared->si ?? [] as $item) {
                $sharedStrings[] = isset($item->t) ? (string) $item->t : collect($item->r ?? [])->map(fn ($run) => (string) $run->t)->implode('');
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw new \RuntimeException('First worksheet not found in XLSX file.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row ?? [] as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $index = $this->columnIndex(preg_replace('/\d+/', '', $ref));
                while (count($values) < $index) {
                    $values[] = '';
                }
                $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ((string) $cell['t'] === 'inlineStr') {
                    $value = (string) ($cell->is->t ?? '');
                }
                $values[] = $value;
            }
            if (array_filter($values, fn ($value) => trim((string) $value) !== '')) {
                $rows[] = $values;
            }
        }

        return $rows;
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split(strtoupper($letters)) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(1, $index);
    }

    private function excelHtml(array $headers, $rows, string $title): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h2>'.e($title).'</h2><table border="1"><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>'.e(str_replace('_', ' ', $header)).'</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>'.e(is_array($value) ? implode('|', $value) : (string) $value).'</td>';
            }
            $html .= '</tr>';
        }

        return $html.'</tbody></table></body></html>';
    }

    private function propertyTypeId($value): int
    {
        $type = is_numeric($value)
            ? PropertyType::find((int) $value)
            : PropertyType::where('slug', $this->slug($value))->orWhere('name', $value)->first();

        if (!$type) {
            throw new \InvalidArgumentException('Property type not found: '.$value);
        }

        return $type->id;
    }

    private function required(array $row, string $key): string
    {
        $value = trim((string) ($row[$key] ?? ''));
        if ($value === '') {
            throw new \InvalidArgumentException("{$key} is required");
        }
        return $value;
    }

    private function slug($value): string
    {
        $slug = Str::slug((string) $value);
        if ($slug === '') {
            throw new \InvalidArgumentException('slug/name is required');
        }
        return $slug;
    }

    private function bool($value): bool
    {
        return in_array(Str::lower((string) $value), ['1', 'true', 'yes', 'active', 'enabled', 'on'], true);
    }

    private function sampleRow(array $headers): array
    {
        return collect($headers)->map(fn ($header) => match ($header) {
            'name' => 'Sample Name',
            'property_type' => 'room',
            'group' => 'amenity',
            'key', 'slug' => 'sample-name',
            'label' => 'Sample Label',
            'reason' => 'Incomplete documents',
            'title' => 'Sample Title',
            'description', 'message' => 'Sample description text',
            'icon' => 'fa-circle-check',
            'badge' => 'Step 1',
            'role' => 'Customer',
            'city' => 'Indore',
            'rating' => 5,
            'status', 'is_active' => 1,
            'is_default' => 0,
            'sort_order' => 0,
            default => '',
        })->all();
    }

    private function exportCatalog(): array
    {
        return [
            'property-types' => ['label' => 'Property Types', 'importable' => true],
            'property-categories' => ['label' => 'Property Categories', 'importable' => true],
            'room-options' => ['label' => 'Property Options / Facilities', 'importable' => true],
            'cities' => ['label' => 'Operational Cities', 'importable' => true],
            'rejection-reasons' => ['label' => 'Rejection Reasons', 'importable' => true],
            'home-features' => ['label' => 'Why Choose Us', 'importable' => true],
            'how-it-works' => ['label' => 'How It Works', 'importable' => true],
            'testimonials' => ['label' => 'Testimonials', 'importable' => true],
            'rooms' => ['label' => 'Properties / Rooms', 'importable' => false],
            'users' => ['label' => 'Users', 'importable' => false],
            'owners' => ['label' => 'Owners', 'importable' => false],
            'staff' => ['label' => 'Admin Staff', 'importable' => false],
            'payments' => ['label' => 'Payments', 'importable' => false],
            'plans' => ['label' => 'Subscription Plans', 'importable' => false],
            'offers' => ['label' => 'Offers', 'importable' => false],
            'blogs' => ['label' => 'Blogs', 'importable' => false],
            'subscribers' => ['label' => 'Subscribers', 'importable' => false],
            'contact-messages' => ['label' => 'Contact Messages', 'importable' => false],
            'city-alerts' => ['label' => 'City Alerts', 'importable' => false],
            'complaints' => ['label' => 'Complaints', 'importable' => false],
            'search-logs' => ['label' => 'Search Logs', 'importable' => false],
            'activity-logs' => ['label' => 'Activity Logs', 'importable' => false],
        ];
    }

    private function importCatalog(): array
    {
        return [
            'property-types' => ['label' => 'Property Types', 'columns' => ['name', 'slug', 'status']],
            'property-categories' => ['label' => 'Property Categories', 'columns' => ['property_type', 'name', 'slug', 'status']],
            'room-options' => ['label' => 'Property Options / Facilities', 'columns' => ['group', 'key', 'label', 'sort_order', 'is_active']],
            'cities' => ['label' => 'Operational Cities', 'columns' => ['name', 'slug', 'state', 'is_active', 'is_default', 'latitude', 'longitude', 'sort_order']],
            'rejection-reasons' => ['label' => 'Rejection Reasons', 'columns' => ['reason', 'is_active']],
            'home-features' => ['label' => 'Why Choose Us', 'columns' => ['title', 'description', 'icon', 'sort_order', 'is_active']],
            'how-it-works' => ['label' => 'How It Works', 'columns' => ['group', 'title', 'description', 'icon', 'badge', 'sort_order', 'is_active']],
            'testimonials' => ['label' => 'Testimonials', 'columns' => ['name', 'role', 'city', 'message', 'rating', 'status', 'sort_order']],
        ];
    }

    private function reportCatalog(): array
    {
        return $this->exportCatalog();
    }
}
