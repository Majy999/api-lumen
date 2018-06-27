<?php

namespace App\Http\Controllers;

use App\Services\WeChatService;

class WorkController extends Controller
{

   public function getAgentDetail()
   {
       $weChatService = new WeChatService();
       $agentDetail = $weChatService->getAccessToken();
       var_dump($agentDetail);
   }
}
