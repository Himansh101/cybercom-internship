<?php
namespace App\Controllers;

use App\Models\Customer;

class ProfileController extends BaseController
{
    public function index()
    {
        $userId = $_SESSION['user_id'];
        $customerModel = new Customer();
        $profile = $customerModel->find($userId);

        if (!$profile) {
            header("Location: login");
            exit();
        }

        $pageTitle = 'EasyCart | My Profile';
        $currentPage = 'profile';
        $extraStyles = ['profile.css'];
        $extraScripts = ['profile.js'];

        $this->render('profile', [
            'profile' => $profile,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
