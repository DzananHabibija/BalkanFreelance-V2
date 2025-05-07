<?php

require_once __DIR__ . '/../services/GigService.php';

Flight::set("gig_service", new GigService());

Flight::route('GET /gigs/@gig_id', function ($id) {
    $service = Flight::get("gig_service");
    $gig = $service->get_gig_by_id($id);
    Flight::json($gig);
});

Flight::route('POST /gigs/add', function() {
    $payload = Flight::request()->data->getData();
    $gig = Flight::get('gig_service')->add_gig($payload);
    Flight::json($gig);
});

Flight::route('GET /gigs', function() {
    $service = Flight::get("gig_service");
    $gigs = $service->get_gigs();
    Flight::json($gigs);
});

Flight::route('DELETE /gigs/delete/@gig_id', function ($id) {
    $service = Flight::get("gig_service");
    $gig = $service->delete_gig($id);
    Flight::json(["message" => "You have successfully deleted the gig!"]);
});

Flight::route('GET /gigs/search/@term', function ($term) {
    $service = Flight::get("gig_service");
    $gigs = $service->search_gigs($term);
    Flight::json($gigs);
});
