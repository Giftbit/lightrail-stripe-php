<?php

namespace Lightrail;

Class LightrailException extends \Exception {}

Class BadParameterException extends LightrailException {}

Class ObjectNotFoundException extends LightrailException {}

Class AuthorizationException extends LightrailException {}