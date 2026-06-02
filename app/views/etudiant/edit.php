<?php
$isEdit = true;
$formAction = url('etudiant', ['action' => 'edit', 'id' => $memoire['id']]);
$title = 'Modifier le mémoire';
require __DIR__ . '/_form.php';
