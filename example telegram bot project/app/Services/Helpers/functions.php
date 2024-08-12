<?php

if (!function_exists('escapeBotChars')) {
    function escapeBotChars($string) : string
    {
        return addcslashes($string, '`_*[]()~>#+-=|{}.!');
    }
}

if (!function_exists('escapeMarkdownV2BotChars')) {
    function escapeMarkdownV2BotChars($string) : string
    {
        return addcslashes($string, '`_*[]()~>#+-=|{}.!');
    }
}

if (!function_exists('escapeMarkdownBotChars')) {
    function escapeMarkdownBotChars($string) : string
    {
        return addcslashes($string, '`_*');
    }
}

if (!function_exists('escapeInsideLinkBotChars')) {
    function escapeInsideLinkBotChars($string) : string
    {
        return addcslashes($string, '(\\');
    }
}
