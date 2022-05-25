<?php
namespace App\Services;
use App\Models\Application;
use App\Models\ApplicationLog;
use Illuminate\Http\Request;

class Logger
{ 

  /**
   * Add an entry 
   * 
   * @param Application $application
   * @param String $action
   */
  public function log(Application $application, $action)
  {
    return ApplicationLog::create(
      [
        'uuid' => \Str::uuid(),
        'action' => $action, 
        'application_id' => $application->id, 
        'user_id' => auth()->user()->id
      ]
    );
  }
}
