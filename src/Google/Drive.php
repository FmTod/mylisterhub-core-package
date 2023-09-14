<?php

namespace MyListerHub\Core\Google;

use Exception;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Drive
{
    /**
     * Google Client.
     *
     * @var \Google\Client
     */
    public $client;

    /**
     * Google Drive service.
     *
     * @var \Google_Service_Drive
     */
    public $drive;

    /**
     * Drive constructor.
     */
    public function __construct($access_token)
    {
        $this->client = new \Google\Service\Drive\Drive(config('services.google'));
        $this->client->setAccessToken($access_token);

        if ($this->client->isAccessTokenExpired() && $this->client->getRefreshToken()) {
            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        }

        $this->drive = new Google_Service_Drive($this->client);
    }

    /**
     * Get current access token as [token, created_at, expires_in].
     */
    public function getAccessToken(): array
    {
        return [
            'access_token' => $this->client->getAccessToken()['access_token'],
            'expires_in' => $this->client->getAccessToken()['expires_in'],
            'created' => $this->client->getAccessToken()['created'],
        ];
    }

    /**
     * List all folders in the directory.
     *
     * @return array|Google_Service_Drive_DriveFile[]
     *
     * @throws Exception
     */
    public function getFolders(string $id = 'root', bool $recursive = true): array
    {
        $result = [];
        $pageToken = null;

        $folders = [];

        do {
            try {
                $parameters = [
                    'fields' => 'nextPageToken, files(id, name)',
                    'q' => "mimeType='application/vnd.google-apps.folder' and '".$id."' in parents and trashed=false",
                ];

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                $result = $this->drive->files->listFiles($parameters);
                foreach ($result->getFiles() as $folder) {
                    if ($folder === null) {
                        continue;
                    }
                    $folders[] = ['id' => $folder->getId(), 'name' => $folder->getName()];
                    if ($recursive) {
                        $child_folders = $this->getFolders($folder->getId(), true);
                        foreach ($child_folders as $child_folder) {
                            $folders[] = ['id' => $child_folder['id'], 'name' => $folder->getName().'/'.$child_folder['name']];
                        }
                    }
                }
                $pageToken = $result->getNextPageToken();
            } catch (Exception $e) {
                $pageToken = null;
                throw new Exception('Something went wrong while trying to list the files');
            }
        } while ($pageToken);

        return $folders;
    }

    /**
     * Upload the specified file.
     *
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function createFile($file, array $opt_params = [], string $parent_id = 'root'): Google_Service_Drive_DriveFile
    {
        $name = is_object($file) ? $file->getClientOriginalName() : $file;
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'parent' => $parent_id,
        ]);

        $content = is_object($file) ? File::get($file) : Storage::get($file);
        $mimeType = is_object($file) ? File::mimeType($file) : Storage::mimeType($file);

        return $this->drive->files->create($fileMetadata, array_merge([
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
        ], $opt_params));
    }

    /**
     * Get details for the specified file.
     */
    public function getFile($file_id, array $parameters = []): Google_Service_Drive_DriveFile
    {
        return $this->drive->files->get($file_id, $parameters);
    }

    /**
     * Set the correct permissions and get the shareable link for the specified file.
     *
     * @return mixed
     */
    public function getShareableLink($file_id, bool $public = false)
    {
        if ($public === true) {
            $permission = new Google_Service_Drive_Permission();
            $permission->setRole('reader');
            $permission->setType('anyone');

            $this->drive->permissions->create($file_id, $permission);
        }

        $file = $this->getFile($file_id, ['fields' => 'webViewLink']);

        return $file->getWebViewLink();
    }

    /**
     * Create a folder with the specified name.
     */
    public function createFolder(string $folder_name, array $opt_params = [], string $parent_id = 'root'): Google_Service_Drive_DriveFile
    {
        $folder_meta = new Google_Service_Drive_DriveFile([
            'name' => $folder_name,
            'parent' => $parent_id,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        return $this->drive->files->create($folder_meta, $opt_params);
    }

    /**
     * Delete file with the specified id.
     */
    public function deleteFileOrFolder(string $id, array $opt_params = []): bool
    {
        try {
            $this->drive->files->delete($id, $opt_params);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
