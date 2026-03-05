<?php
class AuthController extends Controller {

    public function login(): void {
        $this->redirect(BASE_URL . '?url=admin/login');
    }

    public function logar(): void {
        $this->redirect(BASE_URL . '?url=admin/login');
    }

    public function registrar(): void {
        $this->redirect(BASE_URL . '?url=admin/login');
    }

    public function registrarPost(): void {
        $this->redirect(BASE_URL . '?url=admin/login');
    }

    public function logout(): void {
        session_destroy();
        session_start();
        $this->redirect(BASE_URL . '?url=menu/index');
    }
}
