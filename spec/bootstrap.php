<?php
declare(strict_types=1);

//Register autoloading for Doctrine Annotations
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');