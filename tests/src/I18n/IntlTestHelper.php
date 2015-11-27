<?php

// override information about intl

namespace leapsunit\src\I18n {
    use leapsunit\TestCase;

    class IntlTestHelper {
        public static $enableIntl;

        /**
         * emulate disabled intl extension
         *
         * enable it only for tests prefixed with testIntl
         * @param Testcase $test
         */
        public static function setIntlStatus($test)
        {
            static::$enableIntl = null;
            if (strncmp($test->getName(false), 'testIntl', 8) === 0) {
                if (!extension_loaded('intl')) {
                    $test->markTestSkipped('intl extension is not installed.');
                }
                static::$enableIntl = true;
            } else {
                static::$enableIntl = false;
            }
        }

        public static function resetIntlStatus()
        {
            static::$enableIntl = null;
        }
    }
}

namespace Leaps\I18n {
    use leapsunit\src\I18n\IntlTestHelper;

    if (!function_exists('Leaps\I18n\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && IntlTestHelper::$enableIntl !== null) {
                return IntlTestHelper::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}

namespace Leaps\Helper {
    use leapsunit\src\I18n\IntlTestHelper;

    if (!function_exists('Leaps\Helper\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && IntlTestHelper::$enableIntl !== null) {
                return IntlTestHelper::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}

namespace Leaps\Validator {
    use leapsunit\src\I18n\IntlTestHelper;

    if (!function_exists('Leaps\Validator\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && IntlTestHelper::$enableIntl !== null) {
                return IntlTestHelper::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}
