<?php
// session_start();
function getUserSessionData() {
    $session = session();
    return $session->get('userdata');
}

function getPermissions() {
    $session = session();
    return esc($session->get('user_permissions'));
}

function getRoles() {
    $session = session();
    return esc($session->get('user_roles'));
}

function isUserLoggedIn() {
    $session = session();
    return $session->has('logged_in') && $session->get('logged_in');
}