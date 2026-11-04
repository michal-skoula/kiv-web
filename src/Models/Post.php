<?php

namespace App\Models;

use App\Models\Enums\PostStatus;
use Core\ORM\Attributes\Column;
use Core\ORM\Model;
use DateTimeImmutable;

class Post extends Model
{
    protected static string $table = 'posts';

    #[Column]
    public int $id;

    #[Column]
    public string $title;

    #[Column]
    public ?string $body;

    #[Column]
    public PostStatus $status;

    #[Column]
    public bool $is_pinned;

    #[Column]
    public int $view_count;

    #[Column]
    public array $tags;

    #[Column]
    public DateTimeImmutable $created_at;

    #[Column]
    public ?DateTimeImmutable $published_at;
}
