<?php
/**
 * RecoveryController
 * @package api-profile-recovery
 * @version 0.0.1
 */

namespace ApiProfileRecovery\Controller;

use LibForm\Library\Form;
use Profile\Model\Profile;
use LibOtp\Library\Otp;
use LibOtp\Model\Otp as _Otp;
use SiteProfileRecovery\Model\ProfileRecovery as PRecovery;

class RecoveryController extends \Api\Controller
{
    private function sendOtp(object $profile, string $otp): void{

    }

    public function recoveryAction() {
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $form = new Form('api.profile.recovery');

        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        $profile = Profile::getOne([
            '$or' => [
                [
                    'name' => $valid->identity
                ],
                [
                    'email' => $valid->identity
                ],
                [
                    'phone' => $valid->identity
                ]
            ]
        ]);

        if(!$profile){
            $form->addError('identity', '0.0', 'No profile found with that identity');
            return $this->resp(422, $form->getErrors());
        }

        // create OTP to send to profile as verification
        $otp = Otp::generate($profile->id);
        $this->sendOtp($profile, $otp);

        $otp = _Otp::getOne([
            'identity' => $profile->id,
            'otp'      => $otp
        ]);

        $this->resp(0, [
            'profile' => [
                'id' => (int)$profile->id
            ],
            'otp' => [
                'id' => (int)$otp->id
            ]
        ]);
    }

    public function resentAction(){
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $profile_id = $this->req->param->profile;
        $otp_id  = $this->req->param->otp;

        $code    = _Otp::getOne([
            'id'       => $otp_id,
            'identity' => $profile_id
        ]);

        if(!$code)
            return $this->show404();

        $profile = Profile::getOne(['id'=>$profile_id]);
        if(!$profile)
            return $this->show404();

        $this->sendOtp($profile, $code->otp);

        $this->resp(0, 'success');
    }

    public function resetAction(){
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $hash = $this->req->param->hash;
        $recovery = PRecovery::getOne(['hash'=>$hash]);
        if(!$recovery)
            return $this->show404();

        $expire = strtotime($recovery->expires);
        if($expire < time()){
            PRecovery::remove(['id'=>$recovery->id]);
            return $this->show404();
        }

        $form = new Form('api.profile.recovery.reset');

        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        $new_password = password_hash($valid->password, PASSWORD_DEFAULT);

        Profile::set(['password'=>$new_password], ['id'=>$recovery->profile]);

        PRecovery::remove(['id'=>$recovery->id]);

        $this->resp(0, 'success');
    }

    public function verifyAction() {
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $profile_id = $this->req->param->profile;
        $code       = $this->req->param->code;

        if(!Otp::validate($profile_id, $code))
            return $this->show404();

        // create recovery object
        $verif = [
            'profile' => $profile_id,
            'expires' => date('Y-m-d H:i:s', strtotime('+2 hour')),
            'hash'    => ''
        ];
        while(true){
            $verif['hash'] = md5(time() . '-' . uniqid() . '-' . $profile_id);
            if(!PRecovery::getOne(['hash'=>$verif['hash']]))
                break;
        }
        PRecovery::create($verif);

        $this->resp(0, [
            'profile' => [
                'id' => (int)$profile_id
            ],
            'hash' => $verif['hash']
        ]);
    }
}