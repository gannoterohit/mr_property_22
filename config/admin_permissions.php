<?php

return [
    'catalog' => [
        'dashboard.view' => ['Dashboard', 'View admin dashboard and operational summaries'],
        'listings.view' => ['Listings: view', 'View rooms, room options and rejection reasons'],
        'listings.manage' => ['Listings: manage', 'Create, edit, approve, reject or remove listings'],
        'people.view' => ['People: view', 'View users and property owners'],
        'people.manage' => ['People: manage', 'Create owners and block or unblock accounts'],
        'support.view' => ['Support: view', 'View complaints, enquiries, alerts and subscribers'],
        'support.manage' => ['Support: manage', 'Reply, update and remove support records'],
        'finance.view' => ['Finance: view', 'View payments, payouts, plans and offers'],
        'finance.manage' => ['Finance: manage', 'Process payouts and manage subscription plans'],
        'content.view' => ['Content: view', 'View blogs, offers, homepage and CMS pages'],
        'content.manage' => ['Content: manage', 'Create, edit or delete promotional and website content'],
        'reports.view' => ['Reports', 'View reports and search analytics'],
        'reports.manage' => ['Reports: manage', 'Delete search analytics history'],
        'settings.manage' => ['Settings', 'Manage business, integration and maintenance settings'],
        'staff.manage' => ['Staff & roles', 'Create staff and manage role permissions'],
        'activity.view' => ['Activity logs', 'View administrative activity history'],
        'brokers.view' => ['Brokers: view', 'View broker profiles and listings'],
        'brokers.manage' => ['Brokers: manage', 'Approve, reject, suspend or activate brokers'],
        'brokers.settings' => ['Brokers: settings', 'Manage broker module settings and pricing'],
        'brokers.plans.manage' => ['Brokers: plans', 'Manage broker subscription plans'],
    ],
    'roles' => [
        'super_admin' => ['Super Admin', 'Full platform access', ['*']],
    ],
];
