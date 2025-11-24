<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;

class AvatarUploader
{
    public function upload(UploadedFile $file, User $user): string
    {
        // TODO: Implémentation future
        return '';
    }

    public function remove(string $filename): void
    {
        // TODO: Suppression future
    }
}
