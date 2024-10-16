<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // echo "<pre>";
        // print_r(session()->has('username'));
        // print_r(session()->get('username'));
        // echo "</pre>";
        if (! session()->has('username') || ! session()->get('username')) {
            if ($request->uri->getPath() !== '/' && $request->uri->getPath() !== 'deskapp/login/auth') {
                return redirect()->to('/');
            }
        }
    }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}