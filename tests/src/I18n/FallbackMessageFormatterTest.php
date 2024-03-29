<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\I18n;

use Leaps\I18n\MessageFormatter;
use leapsunit\TestCase;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 * @group i18n
 */
class FallbackMessageFormatterTest extends TestCase
{
    const N = 'n';
    const N_VALUE = 42;
    const SUBJECT = 'сабж';
    const SUBJECT_VALUE = 'Answer to the Ultimate Question of Life, the Universe, and Everything';

    public function patterns()
    {
        return [
            [
                '{'.self::SUBJECT.'} is {'.self::N.'}', // pattern
                self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ]
            ],

            [
                '{'.self::SUBJECT.'} is {'.self::N.', number}', // pattern
                self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ]
            ],

            [
                '{'.self::SUBJECT.'} is {'.self::N.', number, integer}', // pattern
                self::SUBJECT_VALUE.' is '.self::N_VALUE, // expected
                [ // params
                    self::N => self::N_VALUE,
                    self::SUBJECT => self::SUBJECT_VALUE,
                ]
            ],

            // This one was provided by Aura.Intl. Thanks!
            [<<<_MSG_
{gender_of_host, select,
  female {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to her party.}
      =2 {{host} invites {guest} and one other person to her party.}
     other {{host} invites {guest} and # other people to her party.}}}
  male {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to his party.}
      =2 {{host} invites {guest} and one other person to his party.}
     other {{host} invites {guest} and # other people to his party.}}}
  other {{num_guests, plural, offset:1
      =0 {{host} does not give a party.}
      =1 {{host} invites {guest} to their party.}
      =2 {{host} invites {guest} and one other person to their party.}
      other {{host} invites {guest} and # other people to their party.}}}}
_MSG_
                ,
                'ralph invites beep and 3 other people to his party.',
                [
                    'gender_of_host' => 'male',
                    'num_guests' => 4,
                    'host' => 'ralph',
                    'guest' => 'beep'
                ]
            ],

            [
                '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Leaps!',
                'Alexander is male and he loves Leaps!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                ],
            ],

            // verify pattern in select does not get replaced
            [
                '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Leaps!',
                'Alexander is male and he loves Leaps!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                    // following should not be replaced
                    'he' => 'wtf',
                    'she' => 'wtf',
                    'it' => 'wtf',
                ]
            ],

            // verify pattern in select message gets replaced
            [
                '{name} is {gender} and {gender, select, female{she} male{{he}} other{it}} loves Leaps!',
                'Alexander is male and wtf loves Leaps!',
                [
                    'name' => 'Alexander',
                    'gender' => 'male',
                    'he' => 'wtf',
                    'she' => 'wtf',
                ],
            ],

            // some parser specific verifications
            [
                '{gender} and {gender, select, female{she} male{{he}} other{it}} loves {nr} is {gender}!',
                'male and wtf loves 42 is male!',
                [
                    'nr' => 42,
                    'gender' => 'male',
                    'he' => 'wtf',
                    'she' => 'wtf',
                ],
            ],
        ];
    }

    /**
     * @dataProvider patterns
     */
    public function testNamedArguments($pattern, $expected, $args)
    {
        $formatter = new FallbackMessageFormatter();
        $result = $formatter->fallbackFormat($pattern, $args, 'en-US');
        $this->assertEquals($expected, $result, $formatter->getErrorMessage());
    }

    public function testInsufficientArguments()
    {
        $expected = '{'.self::SUBJECT.'} is '.self::N_VALUE;

        $formatter = new FallbackMessageFormatter();
        $result = $formatter->fallbackFormat('{'.self::SUBJECT.'} is {'.self::N.'}', [
            self::N => self::N_VALUE,
        ], 'en-US');

        $this->assertEquals($expected, $result);
    }

    public function testNoParams()
    {
        $pattern = '{'.self::SUBJECT.'} is '.self::N;

        $formatter = new FallbackMessageFormatter();
        $result = $formatter->fallbackFormat($pattern, [], 'en-US');
        $this->assertEquals($pattern, $result, $formatter->getErrorMessage());
    }

    public function testGridViewMessage()
    {
        $pattern = 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.';
        $formatter = new FallbackMessageFormatter();
        $result = $formatter->fallbackFormat($pattern, ['begin' => 1, 'end' => 5, 'totalCount' => 10], 'en-US');
        $this->assertEquals('Showing <b>1-5</b> of <b>10</b> items.', $result);
    }
}

class FallbackMessageFormatter extends MessageFormatter
{
    public function fallbackFormat($pattern, $args, $locale)
    {
        return parent::fallbackFormat($pattern, $args, $locale);
    }
}
