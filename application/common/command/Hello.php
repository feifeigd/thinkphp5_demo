<?php
/**
 * Created by PhpStorm.
 * User: luo fei
 * Date: 2018/12/27
 * Time: 12:51
 */

namespace app\common\command;


use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Hello extends Command
{
  // php think hello  名字 --city  城市
  protected function configure()
  {
    parent::configure();
    $this->setName('hello')
      ->addArgument('name', Argument::OPTIONAL, 'your name')
      ->addOption('city', null, Option::VALUE_REQUIRED, 'city name')
      ->setDescription('Say Hello');
  }
  protected function execute(Input $input, Output $output)
  {
    $name = trim($input->getArgument('name')) ?: 'thinkphp';
    $city = ($input->hasOption('city')) ? PHP_EOL . 'From ' . $input->getOption('city'): '';
    $output->writeln("Hello," . $name . '!' . $city);
  }
}
