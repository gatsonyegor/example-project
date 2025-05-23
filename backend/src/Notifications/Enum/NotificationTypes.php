<?php

declare(strict_types=1);

namespace App\Notifications\Enum;

enum NotificationTypes: string
{
	case NEWS_UPDATED = 'news_updated';
	case USER_REGISTERED = 'user_registered';
	case USER_VERIFIED = 'user_verified';
}
