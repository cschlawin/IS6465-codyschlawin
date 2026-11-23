<?php
require_once __DIR__ . '/auth.php';

function hasRole(array $user, array $allowedRoles): bool
{
  return in_array($user['role'], $allowedRoles, true);
}

function requireRole(array $user, array $allowedRoles): void
{
  if (!hasRole($user, $allowedRoles)) {
    show_error('Access denied. Your role cannot use this page.', 403);
  }
}
