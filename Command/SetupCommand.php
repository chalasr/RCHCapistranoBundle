<?php

namespace Chalasdev\CapistranoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;

class SetupCommand extends ContainerAwareCommand
{

   protected function configure()
   {
      $this
      ->setName('capistrano:setup')
      ->setDescription('Setup capistrano deployment configuration in interactive mode');
   }

   protected function interact(InputInterface $input, OutputInterface $output)
   {
      $fs = new Filesystem();
      $helper = $this->getHelper('question');
      $root = $this->getContainer()->get('kernel')->getRootDir();
      if (false !== $fs->exists("{$root}/../config")) {
          $fs->remove("{$root}/../config");
      }
      $fs->mirror(
          $root.'/../vendor/chalasdev/capistrano-bundle/Chalasdev/CapistranoBundle/Resources/config/capistrano', //production
          // $root.'/../src/Chalasdev/CapistranoBundle/Resources/config/capistrano', //development
          $root.'/../config/'
      );
      $path = $root."/../config/";
      $deployRb = $path.'deploy.rb';
      $appPath = explode('/', $this->getContainer()->get('kernel')->getRootDir());
      $appName = $appPath[count($appPath)-2];
      $productionStaging = [];
      $data = [
          'repo_url' => '',
          'ssh_user' => '',
          'deploy_to' => '',
          'branch' => '',
          'model_manager' => '',
          'symfony_env' => '',
          'use_sudo' => '',
          'keep_releases' => '',
      ];
      $properties = [
          "application" => [
              "helper" => $appName,
              "label" => "Repository",
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
          $data[$key] = $helper->ask($input, $output, $question);
      }
      foreach ($data as $k => $v) {
          if (!is_bool($v) && !is_int($v)) {
              if (in_array($v, ['true', 'false'])) {
                  $expression = "set :{$k}, {$v}".PHP_EOL;
              } else {
                  $expression = "set :{$k}, '{$v}'".PHP_EOL;
              }
          } else {
              $expression = "set :{$k}, {$v}".PHP_EOL;
          }
          file_put_contents($deployRb, $expression, FILE_APPEND);
      }
      $output->writeln("<comment>deploy.rb succesfully created.</comment>");

      // Production staging
      $stagingPath = $root."/../config/deploy/production.rb";
      $currentUser = get_current_user();
      $currentOs = php_uname('s');
      $serverOptions = [
          "domain" => [
              "helper" => "example.fr",
              "label" => "Domain name"
          ],
      ];
      $serverProps = [
          "domain" => "",
          "user" => $data["ssh_user"],
      ];
      $question = new Question("<info>{$serverOptions['domain']['label']}</info> [<comment>{$serverOptions['domain']['helper']}</comment>]: ", $serverOptions['domain']['helper']);
      $sshProps["domain"] = $helper->ask($input, $output, $question);
      $expression = PHP_EOL."server '{$sshProps['domain']}',".PHP_EOL."user: '{$data["ssh_user"]}',".PHP_EOL;
      // Remote Server
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
          $sshOptions["keys"]["helper"] = "/home/{$currentUser}/.ssh/id_rsa";
      }
      foreach ($sshOptions as $key => $property) {
          $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
          if (isset($property['autocomplete'])) {
              $question->setAutocompleterValues($property['autocomplete']);
          }
          if (in_array($key, ["auth_methods", "keys"])) {
              $sshProps[$key] = "%w(".$helper->ask($input, $output, $question).")";
          } else {
              $sshProps[$key] = $helper->ask($input, $output, $question);
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

      $output->writeln("<comment>Remote server succesfully configured</comment>");
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
       $fs = new Filesystem();
       $helper = $this->getHelper('question');
       $root = $this->getContainer()->get('kernel')->getRootDir()."/..";
       $fs->touch("{$root}/Capfile");
   }

}
