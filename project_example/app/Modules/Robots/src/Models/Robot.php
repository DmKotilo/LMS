<?php

namespace Robots\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Robots\Observers\RobotObserver;

#[ObservedBy([RobotObserver::class])]
class Robot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
    ];

    /**
     * Получить единственную запись robots.txt
     */
    public static function getSingle()
    {
        return self::first();
    }

    /**
     * Получить содержимое robots.txt
     */
    public static function getContent(): string
    {
        $robot = self::getSingle();
        return $robot ? $robot->content : self::getDefaultContent();
    }

    /**
     * Получить содержимое по умолчанию для robots.txt
     */
    public static function getDefaultContent(): string
    {
        return "User-agent: *\nDisallow:";
    }

    /**
     * Сохранить содержимое в файл robots.txt
     */
    public function saveToFile(): bool
    {
        $publicPath = public_path('robots.txt');

        \Log::info('saveToFile вызван', [
            'robot_id' => $this->id,
            'content_is_set' => isset($this->content),
            'content_length' => isset($this->content) ? strlen($this->content) : 0,
            'public_path' => $publicPath
        ]);

        try {
            // Проверяем, что содержимое установлено
            if (!isset($this->content) || $this->content === null) {
                \Log::error('Содержимое robots.txt не установлено', [
                    'robot_id' => $this->id,
                    'attributes' => $this->getAttributes()
                ]);
                return false;
            }

            // Проверяем, что директория существует и доступна для записи
            $directory = dirname($publicPath);
            if (!is_dir($directory)) {
                \Log::error('Директория не существует: ' . $directory);
                return false;
            }

            if (!is_writable($directory)) {
                \Log::error('Нет прав на запись в директорию: ' . $directory, [
                    'permissions' => substr(sprintf('%o', fileperms($directory)), -4)
                ]);
                return false;
            }

            // Проверяем, существует ли файл и пытаемся изменить права, если нужно
            if (file_exists($publicPath)) {
                $currentPerms = fileperms($publicPath);
                $currentPermsOct = substr(sprintf('%o', $currentPerms), -4);
                
                \Log::info('Файл robots.txt существует', [
                    'path' => $publicPath,
                    'current_permissions' => $currentPermsOct,
                    'is_writable' => is_writable($publicPath),
                    'file_owner' => fileowner($publicPath),
                    'process_uid' => getmyuid()
                ]);

                // Пытаемся установить права на запись, если файл не доступен для записи
                if (!is_writable($publicPath)) {
                    // Пробуем установить права 0664 (rw-rw-r--)
                    $chmodResult = @chmod($publicPath, 0664);
                    \Log::info('Попытка изменить права на файл robots.txt', [
                        'chmod_result' => $chmodResult,
                        'new_permissions' => '0664',
                        'is_writable_after_chmod' => is_writable($publicPath),
                        'actual_permissions_after' => file_exists($publicPath) ? substr(sprintf('%o', fileperms($publicPath)), -4) : 'file_not_exists'
                    ]);
                }
            }

            // Сохраняем файл (попробуем записать, даже если is_writable вернул false)
            // file_put_contents может работать даже если is_writable возвращает false
            // (например, если процесс имеет права через группу)
            $result = @file_put_contents($publicPath, $this->content, LOCK_EX);

            if ($result === false) {
                // Если не удалось записать, пробуем удалить файл и создать заново
                if (file_exists($publicPath)) {
                    \Log::info('Попытка удалить файл robots.txt для пересоздания', [
                        'path' => $publicPath
                    ]);
                    @unlink($publicPath);
                }

                // Пробуем создать файл заново
                $result = @file_put_contents($publicPath, $this->content, LOCK_EX);

                if ($result === false) {
                    $error = error_get_last();
                    \Log::error('Не удалось записать файл robots.txt даже после удаления', [
                        'path' => $publicPath,
                        'error' => $error,
                        'content_length' => strlen($this->content),
                        'directory_writable' => is_writable($directory),
                        'file_exists' => file_exists($publicPath)
                    ]);
                    return false;
                }
            }

            // Проверяем, что файл действительно записался
            if (!file_exists($publicPath)) {
                \Log::error('Файл robots.txt не был создан после записи', [
                    'path' => $publicPath
                ]);
                return false;
            }

            $fileContent = file_get_contents($publicPath);
            if ($fileContent !== $this->content) {
                \Log::error('Содержимое файла robots.txt не совпадает с ожидаемым', [
                    'path' => $publicPath,
                    'expected_length' => strlen($this->content),
                    'actual_length' => strlen($fileContent)
                ]);
                return false;
            }

            \Log::info('Файл robots.txt успешно обновлен', [
                'path' => $publicPath,
                'size' => $result,
                'content_length' => strlen($this->content)
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Ошибка при сохранении robots.txt: ' . $e->getMessage(), [
                'path' => $publicPath,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'content_length' => isset($this->content) ? strlen($this->content) : 'not set',
                'robot_id' => $this->id
            ]);
            return false;
        }
    }
}

