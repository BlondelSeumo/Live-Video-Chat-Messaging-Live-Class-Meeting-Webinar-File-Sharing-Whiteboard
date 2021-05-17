<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\SocialLoginRepository;
use Socialite;

class SocialLoginController extends Controller
{
    protected $repo;

    public function __construct(
        SocialLoginRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Redirect the user to the social login authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from social login.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();

        $this->repo->handle($provider, $user);

        return redirect('/app/panel/dashboard');
    }
}
