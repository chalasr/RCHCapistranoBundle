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
      $path = $this->getContainer()->get('kernel')->getRootDir() . "/../config/";
      $deployRb = $path.'deploy.rb';
      $appPath = explode('/', $this->getContainer()->get('kernel')->getRootDir());
      $appName = $appPath[count($appPath)-2];
      $data = [
          'repo_url' => '',
          'ssh_user' => '',
          'deploy_to' => '',
          'branch' => '',
          'model_manager' => '',
          'scm' => '',
          'symfony_env' => '',
          'app_path' => '',
          'web_path' => '',
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
              "label" => "Branch"
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
          "scm" =>  [
              "helper" => "git",
              "label" => "VCS"
          ],
          "symfony_env" =>  [
              "helper" => "prod",
              "label" => "Environment"
          ],
          "app_path" =>  [
              "helper" => "app",
              "label" => "app folder"
          ],
          "web_path" =>  [
              "helper" => "web",
              "label" => "Web folder"
          ],
          "use_sudo" =>  [
              "helper" => "false",
              "label" => "Use sudo"
          ],
          "keep_releases" =>  [
              "helper" => "3",
              "label" => "Number of releases"
          ]
      ];
      if (false === $fs->exists($path)) {
          $fs->mkdir($path);
      }
      if (false !== $fs->exists($deployRb)) {
          $fs->remove($deployRb);
      }
      $fs->touch($deployRb);
      foreach ($properties as $key => $property) {
          if("deploy_to" == $key && null !== $data['ssh_user']) {
              $property['helper'] = "/home/{$data['ssh_user']}/public_html";
          }
          $question = new Question("<info>{$property['label']}</info> [<comment>{$property['helper']}</comment>]: ", $property['helper']);
          if(isset($property['autocomplete'])) {
              $question->setAutocompleterValues($property['autocomplete']);
          }
          $data[$key] = $helper->ask($input, $output, $question);
      }
      foreach ($data as $k => $v) {
          if(!is_bool($v) && !is_int($v)) {
              $expression = "set :{$k}, '{$v}'".PHP_EOL;
          }
          file_put_contents($deployRb, $expression, FILE_APPEND);
      }
      $output->writeln("<comment>Deployment files succesfully created !</comment>");
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
       $fs = new Filesystem();
       $helper = $this->getHelper('question');
       $root = $this->getContainer()->get('kernel')->getRootDir()."/..";
       $fs->touch("{$root}/Capfile");
   }

}
