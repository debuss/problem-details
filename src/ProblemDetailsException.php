<?php

namespace ProblemDetails;

use Exception;

/**
 * Class ProblemDetailsException
 *
 * This exception is used to represent an error condition with a Problem Details object.
 * It extends the base Exception class and includes a ProblemDetails object.
 */
class ProblemDetailsException extends Exception
{

    public function __construct(
        public ProblemDetails $problem_details
    ) {
        parent::__construct($problem_details->title, $problem_details->status);
    }
}
