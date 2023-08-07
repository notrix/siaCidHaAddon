<?php

namespace Notrix\SiaCid\Command;

use Hoa\Socket\Connection\Connection;
use Hoa\Socket\Node;
use Hoa\Socket\Server;
use Notrix\SiaCid\Cid;
use Notrix\SiaCid\CidEvent;
use Notrix\SiaCid\Exception\InvalidFormatException;
use Notrix\SiaCid\Exception\SiaCidException;
use Notrix\SiaCid\Generator\AbstractResponseGenerator;
use Notrix\SiaCid\Parser\AbstractParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Notrix\SiaCid\Command\SiaServerCommand
 */
class SiaServerCommand extends Command
{
    /**
     * @var AbstractParser
     */
    protected $parser;

    /**
     * @var AbstractResponseGenerator
     */
    protected $generator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Class constructor
     *
     * @param AbstractParser            $parser
     * @param AbstractResponseGenerator $generator
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        AbstractParser $parser,
        AbstractResponseGenerator $generator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->parser = $parser;
        $this->generator = $generator;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('notrix:sia:server')
            ->addOption('protocol', null, InputOption::VALUE_REQUIRED, 'Protocol to listen', 'tcp')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Ip to listen', '0.0.0.0')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Port to listen', '10006')
            ->setDescription('Run server to process SIA requests');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->title($this->getDescription());

        ini_set('memory_limit','256M');

        $connectionString = sprintf(
            '%s://%s:%d',
            $input->getOption('protocol'),
            $input->getOption('ip'),
            $input->getOption('port')
        );
        $server = new Server($connectionString, 60);
        $server->connectAndWait();

        $style->comment('Started server: ' . $connectionString);

// DEBUG:
$cid = new Cid();
$cid->setEvent(401);
$cid->setAccount(1);
$cid->setStatus(3);
$cid->setTime(new \DateTime());
$this->eventDispatcher->dispatch(
    CidEvent::EVENT_RECEIVED,
    new CidEvent($cid)
);
// EOF DEBUG:

        while (true) {
            /** @var Node $node */
            foreach ($server->select() as $node) {
                /** @var Connection $connection */
                $connection = $node->getConnection();

                $rawData = $connection->read(1024);

                $style->note('Got data from client');

                if (empty($rawData)) {
                    $connection->disconnect();
                    $style->note('Client disconnected');

                    continue;
                }

                try {
                    $cid = $this->parser->parse($rawData);

                    $this->eventDispatcher->dispatch(
                        CidEvent::EVENT_RECEIVED,
                        new CidEvent($cid)
                    );
                    $style->success('Data handled successfully');
                } catch (InvalidFormatException $exception) {
                    $style->error($exception->getMessage());
                    continue;
                } catch (SiaCidException $exception) {
                    $style->error($exception->getMessage());

                    $nak = $this->generator->getNakResponse($rawData);
                    $connection->writeAll($nak);
                    continue;
                }

                try {
                    $ack = $this->generator->getAckResponse($rawData);
                    $connection->writeAll($ack);
                    $style->success('ACK sent successfully');
                } catch (SiaCidException $exception) {
                    $style->error($exception->getMessage());
                }
            }
        }
    }
}
