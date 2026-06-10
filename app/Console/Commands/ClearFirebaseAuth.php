<?php

namespace App\Console\Commands;

use App\Traits\FirebaseAuthTrait;
use Illuminate\Console\Command;

class ClearFirebaseAuth extends Command
{

    use FirebaseAuthTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-firebase-auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear out created accounts on firebase auth';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $confirmText = __('Firebase Auth users will be cleared. Max of 1,000 user, so might have to call this command multiple times');
        if (!$this->confirm($confirmText, false)) {
            $this->error('Operation cancelled');
            return 0;
        }

        $firebaseAuth = $this->getFirebaseAuth();
        $users = $firebaseAuth->listUsers();
        $usersUIDs = [];
        $deletedRecords = [];
        $totalDeleted = 0;
        foreach ($users as $user) {
            $usersUIDs[] = $user->uid;
            $deletedRecords[] = [
                "uid" => $user->uid,
                "name" => $user->displayName,
                "email" => $user->email,
                "phoneNumber" => $user->phoneNumber,
            ];

            $totalDeleted++;
        }
        // logger("deleted", $deletedRecords);
        logger("Total Delete:: $totalDeleted");
        $firebaseAuth->deleteUsers($usersUIDs, true);
    }
}