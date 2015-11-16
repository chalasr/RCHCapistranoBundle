<?php

namespace Chalasdev\CapistranoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;


class SetupCommand extends ContainerAwareCommand
{

   protected function configure()
   {
      $this
      ->setName('capistrano:setup')
      ->setDescription('Setup capistrano deployment configuration in interactive mode');
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
   }

   protected function interact(InputInterface $input, OutputInterface $output)
   {
      $fs = new Filesystem();
      $questionHelper = $this->getHelper('question');
      $formatter = $this->getHelper('formatter');
      $style = new OutputFormatterStyle('white', 'blue', array('bold'));
      $output->getFormatter()->setStyle('title', $style);
      $root = $this->getContainer()->get('kernel')->getRootDir();
      $welcome = $formatter->formatBlock("Welcome to chalasdev/capistrano", "title", true);
      $output->writeln(['', $welcome, '', 'This bundle provide automation for your deployment workflow, built on top of <comment>capistrano/symfony</comment> rubygem .', 'Created by Robin Chalas - github.com/chalasr', '']);
      $output->writeln([$formatter->formatSection('SETUP', 'Project settings'), '']);
      $path = $root."/../config/";
      $deployRb = $path.'deploy.rb';
      $appPath = explode('/', $root);
      $appName = $appPath[count($appPath)-2];
      $data = [];
      $properties = [
          "application" => [
              "helper" => $appName,
              "label" => "Application",
              "autocomplete" => [$appName]
          ],
          "repo_url" => [
              "helper" => "git@github.com:{user}/{repo}.git",
              "label" => "Repository",
              "autocomplete" => ["git@github.com:chalasr/{$appName}.git", "git@git.sutunam.com/rchalas/{$appName}.git", "git@git.chaladev.fr:chalasr/{$appName}.git"]
          ],
          "branch" =>  [
              "helper" => "master",
              "label" => "Git branch"
          ],
          "ssh_user" => [
              "helper" => "chalasr",
              "label" => "SSH username"
          ],
          "deploy_to" => [
              "helper" => "",
              "label" => "Remote directory"
          ],
          "model_manager" =>  [
              "helper" => "doctrine",
              "label" => "Model manager"
          ],
          "symfony_env" =>  [
              "helper" => "prod",
              "label" => "Environment"
          ],
          "use_sudo" =>  [
              "helper" => "false",
              "label" => "Use sudo"
          ],
          "use_set_permissions" =>  [
              "helper" => "true",
              "label" => "Set permissions"
          ],
          "permission_method" =>  [
              "helper" => ":chmod",
              "label" => "Permission method",
              "autocomplete" => [
                  ":chmod", ":acl"
              ],
          ],
          "keep_releases" =>  [
              "helper" => 3,
              "label" => "Number of releases"
          ],
      ];
      if (false === $fs->exists($deployRb)) {
          $fs->touch($deployRb);
      }
      foreach ($properties as $key => $property) {
          if ("deploy_to" == $key && null !== $data['ssh_user']) {
              $property['helper'] = "/home/{$data['ssh_user']}/public_html";
          }
          $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
          if (isset($property['autocomplete'])) {
              $question->setAutocompleterValues($property['autocomplete']);
          }
          $data[$key] = $questionHelper->ask($input, $output, $question);
      }
      foreach ($data as $k => $v) {
          if (!is_bool($v) && !is_int($v)) {
              if (in_array($v, ['true', 'false', ':chmod'])) {
                  $expression = "set :{$k}, {$v}".PHP_EOL;
              } else {
                  $expression = "set :{$k}, '{$v}'".PHP_EOL;
              }
          } else {
              $expression = "set :{$k}, {$v}".PHP_EOL;
          }
          file_put_contents($deployRb, $expression, FILE_APPEND);
      }
      $question = new Question("<info>Have composer global installation ?</info> [<comment>Y</comment>]: ", 'Y');
      $question->setAutocompleterValues(['Y', 'N']);
      $haveComposer = $questionHelper->ask($input, $output, $question);
      if ($haveComposer !== 'Y') {
          $downloadComposerTask = file_get_contents("{$root}/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/composer-config.rb");
          file_put_contents($deployRb, $downloadComposerTask, FILE_APPEND);
      }
      $output->writeln(['', " > generating <comment>{$appName}/config/deploy.rb</comment>"]);
      $output->writeln(["<info>Successfully created.</info>", '']);
      $output->writeln([$formatter->formatSection('PRODUCTION', 'Remote server / SSH settings'), '']);
      $stagingPath = $root."/../config/deploy/production.rb";
      $currentUser = get_current_user();
      $currentOs = php_uname('s');
      $serverOptions = [
          "domain" => [
              "helper" => $data['ssh_user'],
              "label" => "Domain name"
          ],
      ];
      $question = new Question("<info>{$serverOptions['domain']['label']}</info> [<comment>{$serverOptions['domain']['helper']}</comment>]: ", $serverOptions['domain']['helper']);
      $sshProps["domain"] = $questionHelper->ask($input, $output, $question);
      $expression = PHP_EOL."server '{$sshProps['domain']}',".PHP_EOL."user: '{$data["ssh_user"]}',".PHP_EOL;
      $sshOptions = [
          "forward_agent" => [
              "label" => "SSH forward_agent",
              "helper" => "false",
          ],
          "auth_methods" => [
              "label" => "SSH auth method",
              "helper" => "publickey password",
          ],
          "keys" => [
              "label" => "Remote SSH key",
              "helper" => ""
          ],
      ];
      $sshProps = [
          "user" => $data['ssh_user'],
          "keys" => "",
          "forward_agent" => false,
          "auth_methods" => "publickey password"
      ];
      if ($currentOs == 'Darwin') {
          $sshOptions["keys"]["helper"] = "/Users/{$currentUser}/.ssh/id_rsa";
      } elseif ($currentOs == 'Linux') {
          $sshOptions["keys"]["helper"] = "/home/{$data['ssh_user']}/.ssh/id_rsa";
      }
      foreach ($sshOptions as $key => $property) {
          $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
          if (isset($property['autocomplete'])) {
              $question->setAutocompleterValues($property['autocomplete']);
          }
          if (in_array($key, ["auth_methods", "keys"])) {
              $sshProps[$key] = "%w(".$questionHelper->ask($input, $output, $question).")";
          } else {
              $sshProps[$key] = $questionHelper->ask($input, $output, $question);
          }
      }
      $expression .= "ssh_options: {".PHP_EOL;
      foreach ($sshProps as $k => $v) {
          if ($k == "user") {
              $expression .= "  {$k}: '{$v}',".PHP_EOL;
          } else {
              $expression .= "  {$k}: {$v},".PHP_EOL;
          }
      }
      $expression .= "}";
      file_put_contents($stagingPath, $expression);

      $output->writeln("<comment>Remote server successfully configured</comment>");
   }
}
