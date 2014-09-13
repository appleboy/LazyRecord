<?php
namespace LazyRecord\Command;
use LazyRecord\Schema\SchemaGenerator;
use LazyRecord\Command\CommandUtils;

/**
 * $ lazy build-schema path/to/Schema path/to/SchemaDir
 *
 */
class BuildJsModelCommand extends \CLIFramework\Command
{

    public function usage()
    {
        return 'build-schema [paths|classes]';
    }

    public function brief()
    {
        return 'build schema files.';
    }

    public function arguments($args) {
        $args->add('file')
            ->isa('file')
            ;
    }

    public function options($opts) 
    {
        $opts->add('f|force','force generate all schema files.');
        parent::options($opts);
    }

    public function execute()
    {
        $logger = $this->getLogger();

        CommandUtils::set_logger($this->logger);
        CommandUtils::init_config_loader();

        $this->logger->debug('Finding schemas...');
        $classes = CommandUtils::find_schemas_with_arguments( func_get_args() );

        CommandUtils::print_schema_classes($classes);

        $this->logger->debug("Initializing schema generator...");

        foreach($classes as $schema) {
            var_dump( $schema ); 
        }

        /*
        $generator = new SchemaGenerator;
        if ( $this->options->force ) {
            $generator->setForceUpdate(true);
        }
        $classMap = $generator->generate($classes);
        */
        $logger->info('Done');
    }

}

