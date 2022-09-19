<?php

declare(strict_types=1);

namespace Auth0\WordPress\Utilities;

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
     * @param null|array<string|int|bool> $select
     */
    public static function option(
        string $element,
        string $name,
        string|int|bool|null $value,
        string $type = 'text',
        string $description = '',
        string $placeholder = '',
        ?array $select = null,
        ?bool $disabled = null
    ): void {
        if (strlen($placeholder) >= 1) {
            $placeholder = ' placeholder="' . $placeholder . '"';
        }

        $disabledString = '';

        if ($disabled !== null && $disabled) {
            $disabledString = ' disabled';
        }

        if ($select !== null && count($select) >= 1) {
            if ($disabled === true) {
                echo '<input type="hidden" name="' . $name . '" value="' . $value . '">';
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

            if (strlen($description) >= 1) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if (in_array($type, self::TREAT_AS_TEXT, true)) {
            echo '<input name="' . $name . '" type="' . $type . '" id="' . $element . '" value="' . $value . '" class="regular-text"' . $placeholder . $disabledString . ' />';

            if (strlen($description) >= 1) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if ($type === 'textarea') {
            echo '<textarea name="' . $name . '" id="' . $element . '" rows="10" cols="50" spellcheck="false" class="large-text code"' . $placeholder . $disabledString . '>' . $value . '</textarea>';

            if (strlen($description) >= 1) {
                echo '<p class="description">' . $description . '</p>';
            }

            return;
        }

        if ($type === 'boolean') {
            echo '<input name="' . $name . '" type="checkbox" id="' . $element . '" value="true" ' . checked(
                (bool) $value,
                'true'
            ) . $disabledString . '/> ' . $description;
        }
    }

    public static function pageBegin(string $title, ?string $action = 'options.php'): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . $title . '</h1>';

        if ($action !== null) {
            echo '<form method="post" action="' . $action . '">';
        }
    }

    public static function pageEnd(): void
    {
        echo '</form>';
        echo '</div>';
    }
}
