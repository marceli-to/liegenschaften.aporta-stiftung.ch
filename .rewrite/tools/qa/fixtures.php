<?php
// php fixtures.php up|down — temporary admin + valid offer for the QA screenshots
$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'liegenschaften.aporta-stiftung.ch.test';
require '/Users/marceli.to/Jamon.digital/Webroot/liegenschaften.aporta-stiftung.ch/vendor/autoload.php';
$app = require '/Users/marceli.to/Jamon.digital/Webroot/liegenschaften.aporta-stiftung.ch/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{User, Collection, CollectionItem, Apartment};
$col = '11111111-1111-4111-8111-111111111111';
$items = ['22222222-2222-4222-8222-222222222221', '22222222-2222-4222-8222-222222222222', '22222222-2222-4222-8222-222222222223'];
if ($argv[1] === 'up') {
  $u = new User(); $u->forceFill(['firstname' => 'QA', 'name' => 'Tester', 'email' => 'qa@example.invalid', 'password' => Hash::make('qa-password-123'), 'role' => 'admin', 'email_verified_at' => now()])->save();
  $e = new User(); $e->forceFill(['firstname' => 'QA', 'name' => 'Editor', 'email' => 'qa-editor@example.invalid', 'password' => Hash::make('qa-password-123'), 'role' => 'editor', 'email_verified_at' => now()])->save();
  $c = Collection::create(['uuid' => $col, 'salutation' => 'Frau', 'firstname' => 'Qa', 'name' => 'Kandidatin', 'email' => 'qa-kandidatin@example.invalid', 'remarks' => "Zeile 1\nZeile 2", 'valid_until' => now()->addDays(30), 'estate_id' => 1]);
  foreach (Apartment::where('state_id', 1)->orderBy('id')->take(3)->get() as $i => $a) {
    CollectionItem::create(['uuid' => $items[$i], 'collection_id' => $c->id, 'apartment_id' => $a->id]);
  }
  echo "up: apartment ", Apartment::orderBy('id')->first()->uuid, "\n";
} else {
  User::where('email', 'like', 'qa%@example.invalid')->delete();
  foreach (Collection::withTrashed()->where('email', 'like', 'qa-%@example.invalid')->get() as $c) {
    CollectionItem::withTrashed()->where('collection_id', $c->id)->forceDelete(); $c->forceDelete();
  }
  DB::table('mail_queue')->where('data', 'like', '%qa-%@example.invalid%')->delete();
  DB::table('tenants')->where('email', 'like', 'qa-%@example.invalid')->delete();
  echo "down\n";
}
