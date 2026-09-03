<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Room;
use App\Models\RoomOption;
use App\Models\PropertyType;
use App\Models\PropertyCategory;

echo "ROOM COUNT: ".Room::count()."\n";
echo "MISSING PROPERTY_TYPE: ".Room::whereNull('property_type_id')->count()."\n";
echo "MISSING PROPERTY_CATEGORY: ".Room::whereNull('property_category_id')->count()."\n";

echo "ROOM OPTIONS (room_type):\n";
foreach (RoomOption::where('group','room_type')->orderBy('id')->get() as $opt) {
    echo $opt->id.' | '.$opt->key.' | '.$opt->label."\n";
}

echo "PROPERTY TYPES:\n";
foreach (PropertyType::orderBy('id')->get() as $type) {
    echo $type->id.' | '.$type->slug.' | '.$type->name.' | '.($type->status ? 'active' : 'inactive')."\n";
}

echo "PROPERTY CATEGORIES:\n";
foreach (PropertyCategory::orderBy('property_type_id')->orderBy('id')->get() as $cat) {
    echo $cat->id.' | '.$cat->property_type_id.' | '.$cat->slug.' | '.$cat->name.' | '.($cat->status ? 'active' : 'inactive')."\n";
}

echo "ROOM DETAILS:\n";
foreach (Room::select('id','title','room_type_option_id','property_type_id','property_category_id')->get() as $room) {
    echo $room->id.' | '.str_replace("\n", ' ', $room->title).' | '.($room->room_type_option_id ?? 'null').' | '.($room->property_type_id ?? 'null').' | '.($room->property_category_id ?? 'null')."\n";
}
