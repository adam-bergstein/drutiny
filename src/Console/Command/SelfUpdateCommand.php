<?php

namespace Drutiny\Console\Command;

use Composer\Semver\Comparator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Self update command.
 */
class SelfUpdateCommand extends DrutinyBaseCommand
{

    const GITHUB_API_URL = 'https://api.github.com';
    const GITHUB_ACCEPT_VERSION = 'application/vnd.github.v3+json';

  /**
   * {@inheritdoc}
   */
    protected function configure()
    {
        $this
        ->setName('self-update')
        ->setDescription('Update Drutiny by downloading latest phar release.');
    }

  /**
   * {@inheritdoc}
   */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = $this->getContainer()->get('logger');

        if (!\Phar::running()) {
          $io->error("This is not a self-upgradable release. Please use the latest Phar release file.");
          return 2;
        }

        $current_version = $this->getApplication()->getVersion();

        $composer_json = json_decode(file_get_contents(DRUTINY_LIB . '/composer.json'), true);

        $current_script = realpath($_SERVER['SCRIPT_NAME']);
        if (!is_writable($current_script)) {
            $io->error("Cannot write to $current_script. Will not be able to apply update.");
            return 3;
        }

        $headers = [
          'User-Agent' => $this->getApplication()->getName() . ' drutiny-phar/' . $current_version,
          'Accept' => self::GITHUB_ACCEPT_VERSION,
          'Accept-Encoding' => 'gzip',
        ];

        try {
            $creds = $this->getContainer()->get('Drutiny\Plugin\GithubPlugin')->load();
            $headers['Authorization'] = 'token ' . $creds['personal_access_token'];
        } catch (\Exception $e) {
          $io->warning($e->getMessage());
        }

        $client = $this->getContainer()->get('Drutiny\Http\Client')->create([
          'base_uri' => self::GITHUB_API_URL,
          'headers' => $headers,
          'decode_content' => 'gzip',
        ]);

        $response = $client->get('repos/' . $composer_json['name'] . '/releases');
        $releases = json_decode($response->getBody(), true);

        $latest_release = current($releases);
        $new_version = $latest_release['tag_name'];

        if (!Comparator::greaterThan($new_version, $current_version)) {
            $io->success("No new updates.");
            return 0;
        }
        $logger->notice('New update available: ' . $new_version);

        $io->confirm('Would you like to download and install the newest version ('.$new_version.')?');

        // Download the first asset.
        $download = reset($latest_release['assets']);

        $tmpfile = tempnam(sys_get_temp_dir(), $download['name']);
        $resource = fopen($tmpfile, 'w');
      // $file_path = fopen(realpath($_SERVER['SCRIPT_NAME']),'w');
        $logger->notice("Downloading {$download['name']}...");

        $response = $client->get('repos/' . $composer_json['name'] . '/releases/assets/' . $download['id'], [
          'headers' => [
            'Accept' => $download['content_type'],
          ],
        ]);

        fwrite($resource, $response->getBody());
        fclose($resource);

        chmod($tmpfile, 0766);

        $logger->notice("New release downloaded to $tmpfile.");

        // if (!rename($tmpfile, $current_script)) {
        //     $logger->error("Could not overwrite $current_script with $tmpfile.");
        //     return 1;
        // }
        $io->success("Updated to $new_version.");
        return 0;
    }
}
