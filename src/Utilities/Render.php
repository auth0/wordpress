<?php

declare(strict_types=1);

namespace Auth0\WordPress\Utilities;

use function count;
use function in_array;

final class Render
{
    /**
     * @var string[]
     */
    private const TREAT_AS_TEXT = [
        'color',
        'date',
        'datetime-local',
        'email',
        'password',
        'month',
        'number',
        'search',
        'tel',
        'text',
        'time',
        'url',
        'week',
    ];

    /**
     * @param null|array<bool|int|string> $select
     * @param string                      $element
     * @param string                      $name
     * @param null|bool|int|string        $value
     * @param string                      $type
     * @param string                      $description
     * @param string                      $placeholder
     * @param ?bool                       $disabled
     */
    public static function option(
        string $element,
        string $name,
        string | int | bool | null $value,
        string $type = 'text',
        string $description = '',
        string $placeholder = '',
        ?array $select = null,
        ?bool $disabled = null,
    ): void {
        if ('' !== $placeholder) {
            $placeholder = ' placeholder="' . $placeholder . '"';
        }

        $disabledString = '';

        if (null !== $disabled && $disabled) {
            $disabledString = ' disabled';
        }

        if (null !== $select && count($select) >= 1) {
            if (true === $disabled) {
                echo '<input type="hidden" name="' . $name . '" value="' . ($value ?? '') . '">';
                echo '<select id="' . $element . '"' . $disabledString . '>';
            } else {
                echo '<select name="' . $name . '" id="' . $element . '"' . $disabledString . '>';
            }

            foreach ($select as $optVal => $optText) {
                $selected = '';

                if ($optVal === $value) {
                    $selected = ' selected';
                }

                echo '<option value="' . $optVal . '"' . $selected . '>' . $optText . '</option>';
            }

            echo '</select>';

            if ('' !== $description) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if (in_array($type, self::TREAT_AS_TEXT, true)) {
            echo '<input name="' . $name . '" type="' . $type . '" id="' . $element . '" value="' . ($value ?? '') . '" class="regular-text"' . $placeholder . $disabledString . ' />';

            if ('' !== $description) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if ('textarea' === $type) {
            echo '<textarea name="' . $name . '" id="' . $element . '" rows="10" cols="50" spellcheck="false" class="large-text code"' . $placeholder . $disabledString . '>' . ($value ?? '') . '</textarea>';

            if ('' !== $description) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if ('boolean' === $type) {
            echo '<input name="' . $name . '" type="checkbox" id="' . $element . '" value="true" ' . checked(
                (bool) $value,
                'true',
            ) . $disabledString . '/> ' . $description;
        }
    }

    public static function pageBegin(string $title, ?string $action = 'options.php'): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . $title . '</h1>';

        if (null !== $action) {
            echo '<form method="post" action="' . $action . '">';
        }
    }

    public static function pageEnd(): void
    {
        echo '</form>';
        echo '</div>';
    }
}
