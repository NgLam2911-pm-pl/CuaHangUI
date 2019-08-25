<?php

 
/*
* CuaHangUI plugins are used to exchange items, items are set in config
* Copyright (C) 2019  phuongaz <clonevcc1@gmail.com>
* Connect to receive information about the following updates
* Facebook: https://facebook.com/JustOnly.PhuongCongTu
* Contact number: 0386473400
* Moded by LamPocketVN
*/

namespace phuongaz\CuaHangUI;

use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;

use onebone\economyapi\EconomyAPI;
use onebone\pointapi\PointAPI;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;

Class Main extends PluginBase implements Listener{

    const PREFIX = TF::BOLD. TF::YELLOW. "⚒ " . TF::GREEN."CỬA HÀNG".TF::YELLOW. "⚒";
    private $trades;
    private $setting;
	private $piggyCE;

    /**
     * @return mixed
     */
    public function getTrades()
    {
        return $this->trades->getAll();
    }

    /**
     * @return mixed
     */
    public function getSetting($setting)
    {
        return $this->setting->get($setting);
    }

    public function onLoad()
    {

    }
    public function onEnable() :void
    {

          @mkdir($this->getDataFolder());     
          $this->saveResource("setting.yml");
          $this->saveResource("trades.yml");             

        $this->setting = new Config($this->getDataFolder(). 'setting.yml', Config::YAML);
        $this->trades = new Config($this->getDataFolder(). 'trades.yml', Config::YAML);
        $this->getLogger()->info("\n §l§b•§c CuaHangUI System By Phuongaz | Modded by LamPocketVN \n");
		$this->piggyCE = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
    }
    public function getTrade($id)
    {
        return $this->trades->get($id);
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) :bool
    {
      if($cmd->getName() == "cuahang"){
          if($sender instanceof Player){
              $this->openTradeForm($sender);
          }else{
              $all = $this->getTrades();
			  if(isset($args[0])){
          $trade = $this->trades->getAll[$args[0]];
          var_dump($trade);
        }
			  foreach(array_keys($this->trades->getAll()) as $trades){
			
				$bt = $all[$trades]["Button"];
				$sender->sendMessage("$bt");
				 
              }
          }
      }
	  return true;
    }
    public function openTradeForm($player)
    {
        $form = new SimpleForm(function(Player $player, $data){
            if(is_null($data)) return true;
            $id = (int)$data;
            if($id == 0) return true;
            
            $md = new ModalForm(function(Player $player, $mdata) use ($id){
                if(is_null($mdata)) return true;
                  if($mdata == true){ 
                      $this->TradeItem($player, $id);
                  }
            });
          $md->setTitle(self::PREFIX);
          $content = $this->trades->get($id)['Content'];
          $md->setContent($content);
          $md->setButton1($this->getSetting('Button-1'));
          $md->setButton2($this->getSetting('Button-2'));
          $md->sendToPlayer($player);

        });

        $form->setTitle(self::PREFIX);
        $form->addButton($this->getSetting('Exit-Button'));
        $all = $this->getTrades();
        foreach(array_keys($this->trades->getAll()) as $trades){
			
			  $bt = $all[$trades]["Button"];
            $form->addButton("$bt");
        }
        $form->sendToPlayer($player);
    }

    public function TradeItem($player, int $id)
    {   
      $inv = $player->getInventory();
        if (($this->getTrade($id)['support']['money']) == "true")
		{
			$money = $this->getTrade($id)['price']['money'];
			if((EconomyAPI::getInstance()->myMoney($player)) >= $money){
				$newitem = $this->getTrade($id)['New-Item']['Id'];
				$exn = explode(':', $newitem);
				$idn = $exn[0];
				$metan = $exn[1];
				$countn = $exn[2];
				$new = Item::get($idn, $metan, $countn);
				if($this->getTrade($id)['New-Item']['Name'] != ""){
					$new->setCustomName($this->getTrade($id)['New-Item']['Name']);
				}
				if($this->getTrade($id)['New-Item']['Lore'] != ""){
					$new->setLore(array($this->getTrade($id)['New-Item']['Lore']) );
				}
				$enchantment = $this->getTrade($id)['Enchantments'];
				$tr = $this->getTrades();
				if (isset($enchantment)){
					foreach(array_keys($this->getTrades()[$id]['Enchantments']) as $all){
						$idec = $tr[$id]['Enchantments'][$all]['Id'];
						$level = $tr[$id]['Enchantments'][$all]['Level'];
						$this->enchantItem($new, $level, $idec);
						
					}
				}
				EconomyAPI::getInstance()->reduceMoney($player, $money);
				$player->sendMessage(self::PREFIX. TF::GREEN." Mua vật phẩm thành công");
				$inv->addItem($new);
			}else{
			$player->sendMessage(self::PREFIX. TF::RED. " Bạn không đủ tiền để mua vật phẩm này");
			}
		}
		if (($this->getTrade($id)['support']['point']) == "true")
		{
			$point = $this->getTrade($id)['price']['point'];
			if((PointAPI::getInstance()->myPoint($player)) >= $point){
				$newitem = $this->getTrade($id)['New-Item']['Id'];
				$exn = explode(':', $newitem);
				$idn = $exn[0];
				$metan = $exn[1];
				$countn = $exn[2];
				$new = Item::get($idn, $metan, $countn);
				if($this->getTrade($id)['New-Item']['Name'] != ""){
					$new->setCustomName($this->getTrade($id)['New-Item']['Name']);
				}
				if($this->getTrade($id)['New-Item']['Lore'] != ""){
					$new->setLore(array($this->getTrade($id)['New-Item']['Lore']) );
				}
				$enchantment = $this->getTrade($id)['Enchantments'];
				$tr = $this->getTrades();
				if (isset($enchantment)){
					foreach(array_keys($this->getTrades()[$id]['Enchantments']) as $all){
						$idec = $tr[$id]['Enchantments'][$all]['Id'];
						$level = $tr[$id]['Enchantments'][$all]['Level'];
						$this->enchantItem($new, $level, $idec);
						
					}
				}
				$player->sendMessage(self::PREFIX. TF::GREEN." Mua vật phẩm thành công");
				PointAPI::getInstance()->reducePoint($player, $point);
				$inv->addItem($new);
			}else{
			$player->sendMessage(self::PREFIX. TF::RED. " Bạn không đủ tiền để mua vật phẩm này");
			}
		}

    }
	public function enchantItem($item, int $level, $enchantment): void
	{
        if(is_string($enchantment)){
            $ench = Enchantment::getEnchantmentByName((string) $enchantment);
            if($this->piggyCE !== null && $ench === null){
                $ench = CustomEnchants::getEnchantmentByName((string) $enchantment);
            }
            if($this->piggyCE !== null && $ench instanceof CustomEnchants){
                $this->piggyCE->addEnchantment($item, $ench->getName(), (int) $level);
            }else{
                $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
            }
        }
        if(is_int($enchantment)){
            $ench = Enchantment::getEnchantment($enchantment);
            $item->addEnchantment(new EnchantmentInstance($ench, (int) $level));
        }
    }


}