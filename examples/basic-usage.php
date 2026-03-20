<?php

declare(strict_types=1);

/**
 * Example: Using symfony/polyfill-intl-icu.
 *
 * This polyfill provides a subset of PHP's intl extension (ICU) for
 * environments where the extension is not available. Covers NumberFormatter,
 * IntlDateFormatter, Locale, and Collator with common operations.
 *
 * Install:
 *   composer require symfony/polyfill-intl-icu
 */

// --- NumberFormatter: format numbers for a locale ---
$formatter = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
echo $formatter->format(1234567.89); // "1,234,567.89"

$currencyFormatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
echo $currencyFormatter->formatCurrency(9.99, 'USD'); // "$9.99"

$percentFormatter = new NumberFormatter('en_US', NumberFormatter::PERCENT);
echo $percentFormatter->format(0.753); // "75%"

// --- NumberFormatter: spell out numbers ---
$spellFormatter = new NumberFormatter('en_US', NumberFormatter::SPELLOUT);
echo $spellFormatter->format(42); // "forty-two"

// --- NumberFormatter: parse a formatted number ---
$parsed = $formatter->parse('1,234,567.89');
var_dump($parsed); // float(1234567.89)

// --- Locale: get display name and language ---
$displayName = Locale::getDisplayName('fr_FR', 'en');
var_dump($displayName); // string "French (France)"

$language = Locale::getPrimaryLanguage('fr_FR');
var_dump($language); // string "fr"

$region = Locale::getRegion('fr_FR');
var_dump($region); // string "FR"

// --- Locale: parse and compose locale strings ---
$parsed = Locale::parseLocale('zh_Hans_CN');
var_dump($parsed);
// array ['language' => 'zh', 'script' => 'Hans', 'region' => 'CN']

$composed = Locale::composeLocale(['language' => 'zh', 'script' => 'Hans', 'region' => 'CN']);
var_dump($composed); // string "zh_Hans_CN"

// --- Collator: locale-aware string comparison ---
$collator = new Collator('en_US');

$result = $collator->compare('apple', 'banana');
var_dump($result < 0); // bool(true) — "apple" comes before "banana"

$words = ['banana', 'apple', 'cherry'];
$collator->sort($words);
var_dump($words); // array ['apple', 'banana', 'cherry']

// --- IntlDateFormatter: format dates for a locale ---
$dateFormatter = new IntlDateFormatter(
    'en_US',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'America/New_York',
    IntlDateFormatter::GREGORIAN
);

$formatted = $dateFormatter->format(mktime(0, 0, 0, 3, 15, 2024));
echo $formatted; // "March 15, 2024"
