<?php

	/**
	 * Carrega os arquivos mapeados no itens-mapping.xml na sua devida pagina
	 *
	 */
	class RenderPortlet {
		
		var $mapedPortlets;
		var $ERRO_AT_LOADING_MAPPING = "The mapping.xml was not found.";
		var $NOT_FOUND_ITENS = "Was not found content for this render-id.";
		
		function listener() {
			$render = $_REQUEST["render"];
			$this->renderById($render?$render:"main");
		}
		
		/**
		 * Constructor
		 */
		function RenderPortlet($mapedPortlets = "content-mapping.xml") {
			$this->mapedPortlets = $mapedPortlets;
		}
		
		function renderJavascript($id) {
			$path = $id.".min.js";
			
			if (file_exists(SITE_PATH."/portlets-javascript/build/".$path)) {
				scriptLoader($path);
			}
		}
		
		function renderById($id, $base = "./") {
			
			$arrayMapping = xml2array($this->mapedPortlets);
			
			if (!$arrayMapping) {
				echo $this->ERRO_AT_LOADING_MAPPING;
				return;
			}
			
			$mappingCollection = null;
			$foundItens = false;
			
			foreach ($arrayMapping['mapping'] as $mapping) {
				
				if (is_array($mapping))	{
					$mapId = $mapping['id'];
					$mappingCollection = $mapping;
				}
				else{
					$mapId = $mapping;
					$mappingCollection = $arrayMapping['mapping'];
				}
				
				if ($id == $mapId) {
					
					$foundItens = true;
					
					foreach ($mappingCollection['item'] as $x => $item) {
						
						if (is_array($item)) {
							$fileNameOfItem = $item['file'];	
						}else{
							$fileNameOfItem = $item;
						}
						
						if (!file_exists($fileNameOfItem)) return false;
						
						//$this->renderJavascript($id);
						
						include($fileNameOfItem);
						
					}
					
				}
				
			}
			
			if (!$foundItens) {
				echo $this->NOT_FOUND_ITENS;
			}
		}
		
	}

?>