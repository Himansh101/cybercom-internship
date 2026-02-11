<?php
namespace App\Controller;

use App\Model\Customer\Model_Customer;
use App\View\View_Profile as ProfileView;

class Controller_Profile
{
    public function indexAction()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: login");
            exit;
        }

        $customerModel = new Model_Customer();
        $profile = $customerModel->load($userId)->getData();

        if (!$profile) {
            header("Location: login"); // Or logout
            exit;
        }

        // Pass global variables required for header
        global $cartQuantity;

        $view = new ProfileView();
        echo $view->toHtml('index', [
            'profile' => $profile,
            'user' => $profile, // Header uses $user
            'isLoggedIn' => true, // We verified session above
            'cartQuantity' => $cartQuantity ?? 0,
            'pageTitle' => 'EasyCart | My Profile',
            'currentPage' => 'profile',
            'extraStyles' => ['profile.css'],
            'extraScripts' => ['profile.js']
        ]);
    }
}
