<?php

namespace App\Controllers\Deskapp;
use App\Controllers\BaseController;

class Wizard extends BaseController
{
    public function index()
    {
        return view('/deskapp/wizard/step1');
    }

    public function step1()
    {
        $validation = \Config\Services::validation();

        // Validar datos del paso 1
        $this->validate([
            'nombre' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return view('/deskapp/wizard/step1', ['validation' => $validation]);
        }

        // Guardar datos del paso 1 en la sesión o base de datos
        session()->set('step1', $this->request->getPost());

        return redirect()->to('/deskapp/wizard/step2');
    }

    public function step2()
    {
        $validation = \Config\Services::validation();

        // Validar datos del paso 2
        $this->validate([
            'email' => 'required|valid_email',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return view('/deskapp/wizard/step2', ['validation' => $validation]);
        }

        // Guardar datos del paso 2 en la sesión o base de datos
        session()->set('step2', $this->request->getPost());

        return redirect()->to('/deskapp/wizard/step3');
    }

    public function step3()
    {
        $validation = \Config\Services::validation();

        // Validar datos del paso 3
        $this->validate([
            'telefono' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return view('/deskapp/wizard/step3', ['validation' => $validation]);
        }

        // Guardar datos del paso 3 en la sesión o base de datos
        session()->set('step3', $this->request->getPost());

        return redirect()->to('/deskapp/wizard/complete');
    }

    public function complete()
    {
        // Recuperar los datos guardados de cada paso
        $step1Data = session()->get('step1');
        $step2Data = session()->get('step2');
        $step3Data = session()->get('step3');

        // Aquí puedes procesar todos los datos recopilados
        // por ejemplo, guardarlos en la base de datos.

        return view('/deskapp/wizard/complete', [
            'step1' => $step1Data,
            'step2' => $step2Data,
            'step3' => $step3Data,
        ]);
    }
}
