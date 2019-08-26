<?php 
namespace App\Http\Services;

class CallerIdsService{

	/**
	 * When removing caller id from user, we need to 
	 * Disable all the snippets that has only that 1 caller id
	 * We also need to pause all the messages that has that callerId 
	 * We also will send email to the old user notifying about the action
	 *
	 * @param User $user
	 * @param CallerId $callerId
	 * @return void 
	 */
	public function handleRemovingCallerIdFromUser($user, $callerId, $isAddingToOtherAccount = 0)
	{
		//Check the snippets of the caller id which has only 1 callerId (it means the one that we are removing)
		//and mark them as blocked .
		$callerId->snippet()->whereHas('callerId', function($query){}, '=', 1)->update(['is_blocked' => 1]);
        //Detach this callerId from all snippets
        $callerId->snippet()->detach();
        //Mark the messages that using the callerId as caller_id or transfer_option and mark them as paused

//        $user->campaigns()->where('caller_id', $callerId->phone_number)
//        	->orWhere('transfer_option', 'LIKE', '%' . $callerId->phone_number . '%')
//        	->update(['status' => 'saved']);


        $user->campaigns()->where(function ($query) use($callerId){
            $query->where('caller_id', $callerId->phone_number)
                ->orWhere('transfer_option', 'LIKE', '%' . $callerId->phone_number . '%');
        })->where(function($query){
            $query->where('status', 'scheduled');
        })->update(['status' => 'saved']);

        $user->campaigns()->where(function ($query) use($callerId){
            $query->where('caller_id', $callerId->phone_number)
                ->orWhere('transfer_option', 'LIKE', '%' . $callerId->phone_number . '%');
        })->where(function($query){
            $query->where('status', 'start');
        })->update(['status' => 'stop']);



        //Send the email to the old owner of the callerId, notifying that it will be removed from its account .
        if($isAddingToOtherAccount) {
        	$olduserEmail = $callerId->user ? $callerId->user->email : 'Unknown';
        	$emailRepo = new \App\Services\SendEmailService();
        	$emailRepo->callerIdAddedToOtherAccount($user, $callerId->phone_number);
        }
	}

}