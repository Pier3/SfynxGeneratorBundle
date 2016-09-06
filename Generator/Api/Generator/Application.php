<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Api\Generator;


/**
 * Class Application
 *
 * @category Generator
 * @package Api
 * @subpackage Generator
 */
class Application extends LayerAbstract
{
    /** @var string */
    protected static $handlersFileName = 'application.yml';

    /** @var string */
    protected static $skeletonDir = 'Api/Application';

    /**
     * Entry point of the generation of the "Application" layer in DDD.
     * Call the generation of the Commands, the Queries and the Tests of the "Application" layer.
     */
    public function generate()
    {
        $this->output->writeln('');
        $this->output->writeln('##############################################');
        $this->output->writeln('#       GENERATE APPLICATION STRUCTURE       #');
        $this->output->writeln('##############################################');
        $this->output->writeln('');

        try {
            $this->output->writeln('### COMMANDS GENERATION ###');
            $this->generateCommands();
            $this->output->writeln('### QUERIES GENERATION ###');
            $this->generateQueries();
        } catch (\InvalidArgumentException $e) {
            fwrite(STDERR, $e->getMessage());
            exit;
        }
    }

    /**
     * Generate the Command part in the "Application" layer.
     * @throws \InvalidArgumentException
     */
    protected function generateCommands()
    {
        foreach ($this->commandsQueriesList[self::COMMAND] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];
            $this->parameters['constructorArgs'] = $this->buildConstructorParamsString($data['entity']);

            $this->addHandlers(
                'commandHandler',
                'commandHandlerDecoratorHandler',
                'commandHandlerHandler',
                'commandSpecHandler',
                'commandValidationHandler'
            );
        }

        $this->generator->execute()->clear();
    }

    /**
     * Generate the Query part in the "Application" layer.
     * @throws \InvalidArgumentException
     */
    protected function generateQueries()
    {
        foreach ($this->commandsQueriesList[self::QUERY] as $data) {
            $this->parameters['actionName'] = ucfirst($data['action']);
            $this->parameters['entityName'] = ucfirst(strtolower($data['entity']));
            $this->parameters['entityFields'] = $this->entitiesToCreate[$data['entity']];

            $this->addHandlers('queryHandler', 'queryHandlerHandler');
        }

        $this->generator->execute()->clear();
    }

}
