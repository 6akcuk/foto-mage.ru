<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 28.10.12
 * Time: 20:45
 * To change this template use File | Settings | File Templates.
 */

class ClientScript extends CClientScript {
    public $cssFiles = array();
    public $scriptFiles = array();

    /**
     * Renders the registered scripts.
     * This method is called in {@link CController::render} when it finishes
     * rendering content. CClientScript thus gets a chance to insert script tags
     * at <code>head</code> and <code>body</code> sections in the HTML output.
     * @param string $output the existing output that needs to be inserted with script tags
     */
    public function render(&$output)
    {
        if(!$this->hasScripts)
            return;

        $this->renderCoreScripts();

        if(!empty($this->scriptMap))
            $this->remapScripts();

        $this->unifyScripts();
        $this->orderScripts();

        $this->renderHead($output);
        if($this->enableJavaScript)
        {
            $this->renderBodyBegin($output);
            $this->renderBodyEnd($output);
        }
    }

    public function renderAjax() {
      if (!$this->hasScripts)
        return array();

      $this->unifyScripts();
      $this->orderScripts();

      $staticFiles = array();

      foreach ($this->cssFiles as $css) {
        $al = $this->parseAjaxRepresentation($css['url']);
        $staticFiles[] = $al['scriptname'] .'?'. $al['version'];// array($al['url'], $al['scriptname'], $al['version']);
      }

      foreach ($this->scriptFiles as $pos => $scripts) {
        foreach ($scripts as $key => $script) {
          $al = $this->parseAjaxRepresentation($script['url']);
          $staticFiles[] = $al['scriptname'] .'?'. $al['version'];// array($al['url'], $al['scriptname'], $al['version']);
        }
      }

      return $staticFiles;
    }

    protected function unifyScripts()
    {
        if(!$this->enableJavaScript)
            return;
        $map=array();
        if(isset($this->scriptFiles[self::POS_HEAD]))
            $map=$this->scriptFiles[self::POS_HEAD];

        if(isset($this->scriptFiles[self::POS_BEGIN]))
        {
            foreach($this->scriptFiles[self::POS_BEGIN] as $key=>$scriptFile)
            {
                if(isset($map[$scriptFile['url']]))
                    unset($this->scriptFiles[self::POS_BEGIN][$key]);
                else
                    $map[$scriptFile['url']]=true;
            }
        }

        if(isset($this->scriptFiles[self::POS_END]))
        {
            foreach($this->scriptFiles[self::POS_END] as $key=>$scriptFile)
            {
                if(isset($map[$scriptFile['url']]))
                    unset($this->scriptFiles[self::POS_END][$key]);
            }
        }
    }

    protected function parseAjaxRepresentation($url) {
        $path = explode('/', $url);
        $file = preg_replace("/\?v=\d+/ui", '', end($path));
        preg_match("/\?v=(\d+)/ui", end($path), $v);

        return array(
            'scriptname' => $file,
            'version' => (isset($v[1])) ? $v[1] : 1,
            'url' => $url,
        );
    }

    protected function orderCss()
    {
        foreach ($this->cssFiles as $key => $script) {
            if (!$script['priority'])
                continue;

            $priority = trim($script['priority']);
            $priorities = (strstr($priority, ',')) ? explode(",", $priority) : array($priority);

            foreach ($priorities as $priority) {
                if ($priority[0] == 'b') {
                    $move = 'before';
                    $scriptName = substr($priority, 7);
                }
                elseif ($priority[0] == 'a') {
                    $move = 'after';
                    $scriptName = substr($priority, 6);
                }

                $tpos = $this->getKeyScriptInList($this->cssFiles, $scriptName);
                if (($move == 'after' && $tpos > $key) || ($move == 'before' && $tpos < $key)) {
                    $needAnotherStep = true;

                    $buffer = $this->cssFiles[$key];
                    $scripts[$key] = $this->cssFiles[$tpos];
                    $scripts[$tpos] = $buffer;

                    $buffer = null;
                }
            }
        }
    }

    protected function orderScripts()
    {
      $needAnotherStep = true;
      if (!$this->scriptFiles) return;

        foreach ($this->scriptFiles as $position => &$scripts) {
            while ($needAnotherStep) {
                $needAnotherStep = false;

                foreach ($scripts as $key => $script) {
                    if (!$script['priority'])
                        continue;

                    $priority = trim($script['priority']);
                    $priorities = (strstr($priority, ',')) ? explode(",", $priority) : array($priority);

                    foreach ($priorities as $priority) {
                        if ($priority[0] == 'b') {
                            $move = 'before';
                            $scriptName = substr($priority, 7);
                        }
                        elseif ($priority[0] == 'a') {
                            $move = 'after';
                            $scriptName = substr($priority, 6);
                        }

                        $tpos = $this->getKeyScriptInList($scripts, $scriptName);

                        if (($move == 'after' && $tpos > $key) || ($move == 'before' && $tpos < $key)) {
                          $needAnotherStep = true;

                            $buffer = $scripts[$key];
                            $scripts[$key] = $scripts[$tpos];
                            $scripts[$tpos] = $buffer;

                            $buffer = null;
                        }
                    }
                }
            }
        }
    }

    protected function getKeyScriptInList($map, $name) {
        foreach ($map as $key => $data) {
            if (preg_match('/('. preg_quote($name) .')/ui', $data['url'])) return $key;
        }

        return false;
    }

    protected function remapScripts()
    {
        $cssFiles=array();
        foreach($this->cssFiles as $url=>$media)
        {
            $name=basename($url);
            if(isset($this->scriptMap[$name]))
            {
                if($this->scriptMap[$name]!==false)
                    $cssFiles[$this->scriptMap[$name]]=$media;
            }
            else if(isset($this->scriptMap['*.css']))
            {
                if($this->scriptMap['*.css']!==false)
                    $cssFiles[$this->scriptMap['*.css']]=$media;
            }
            else
                $cssFiles[$url]=$media;
        }
        $this->cssFiles=$cssFiles;

        $jsFiles=array();
        foreach($this->scriptFiles as $position=>$scripts)
        {
            $jsFiles[$position] = array();
            foreach($scripts as $key=>$script)
            {
                $name=basename($script['url']);
                if(isset($this->scriptMap[$name]))
                {
                    if($this->scriptMap[$name]!==false)
                        $jsFiles[$position][$this->scriptMap[$name]]=$this->scriptMap[$name];
                }
                else if(isset($this->scriptMap['*.js']))
                {
                    if($this->scriptMap['*.js']!==false)
                        $jsFiles[$position][$this->scriptMap['*.js']]=$this->scriptMap['*.js'];
                }
                else
                    $jsFiles[$position][$key]=$script;
            }
        }
        $this->scriptFiles=$jsFiles;
    }

    /**
     * Renders the specified core javascript library.
     */
    public function renderCoreScripts()
    {
        if($this->coreScripts===null)
            return;
        $cssFiles=array();
        $jsFiles=array();
        foreach($this->coreScripts as $name=>$package)
        {
            $baseUrl=$this->getPackageBaseUrl($name);
            if(!empty($package['js']))
            {
                foreach($package['js'] as $js)
                    $jsFiles[] = array('url' => $baseUrl.'/'.$js, 'priority' => null);
            }
            if(!empty($package['css']))
            {
                foreach($package['css'] as $css)
                    $cssFiles[$baseUrl.'/'.$css]='';
            }
        }
        // merge in place
        if($cssFiles!==array())
        {
            foreach($this->cssFiles as $cssFile=>$media)
                $cssFiles[$cssFile]=$media;
            $this->cssFiles=$cssFiles;
        }
        if($jsFiles!==array())
        {
            if(isset($this->scriptFiles[$this->coreScriptPosition]))
            {
                foreach($this->scriptFiles[$this->coreScriptPosition] as $url)
                    $jsFiles[] = array('url' => $url['url'], 'priority' => null);
            }
            $this->scriptFiles[$this->coreScriptPosition]=$jsFiles;
        }
    }

    /**
     * Inserts the scripts in the head section.
     * @param string $output the output to be inserted with scripts.
     */
    public function renderHead(&$output)
    {
        $staticFiles = array();
        $html='';
        foreach($this->metaTags as $meta)
            $html.=CHtml::metaTag($meta['content'],null,null,$meta)."\n";
        foreach($this->linkTags as $link)
            $html.=CHtml::linkTag(null,null,null,null,$link)."\n";
        foreach($this->cssFiles as $css) {
            $html.=CHtml::cssFile($css['url'],$css['media'])."\n";

            $al = $this->parseAjaxRepresentation($css['url']);
            $staticFiles[] = "'". $al['scriptname'] ."': {v: ". $al['version'] .", l: 1}";
        }
        foreach($this->css as $css) {
            $html.=CHtml::css($css[0],$css[1])."\n";

        }
        if($this->enableJavaScript)
        {
            if(isset($this->scriptFiles[self::POS_HEAD]))
            {
                foreach($this->scriptFiles[self::POS_HEAD] as $scriptFile) {
                    $html.=CHtml::scriptFile($scriptFile['url'])."\n";

                    $al = $this->parseAjaxRepresentation($scriptFile['url']);
                    $staticFiles[] = "'". $al['scriptname'] ."': {v: ". $al['version'] ."}";
                }
            }

            if(isset($this->scripts[self::POS_HEAD]))
                $html.=CHtml::script(implode("\n",$this->scripts[self::POS_HEAD]))."\n";
        }

        if($html!=='')
        {
            $html = CHtml::script('var StaticFiles = {
                '. implode(",\n", $staticFiles)  .'
            };') . $html;

            $count=0;
            $output=preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is','<###head###>$1',$output,1,$count);
            if($count)
                $output=str_replace('<###head###>',$html,$output);
            else
                $output=$html.$output;
        }
    }

    /**
     * Inserts the scripts at the beginning of the body section.
     * @param string $output the output to be inserted with scripts.
     */
    public function renderBodyBegin(&$output)
    {
        $html='';
        if(isset($this->scriptFiles[self::POS_BEGIN]))
        {
            foreach($this->scriptFiles[self::POS_BEGIN] as $scriptFile)
                $html.=CHtml::scriptFile($scriptFile['url'])."\n";
        }
        if(isset($this->scripts[self::POS_BEGIN]))
            $html.=CHtml::script(implode("\n",$this->scripts[self::POS_BEGIN]))."\n";

        if($html!=='')
        {
            $count=0;
            $output=preg_replace('/(<body\b[^>]*>)/is','$1<###begin###>',$output,1,$count);
            if($count)
                $output=str_replace('<###begin###>',$html,$output);
            else
                $output=$html.$output;
        }
    }

    /**
     * Inserts the scripts at the end of the body section.
     * @param string $output the output to be inserted with scripts.
     */
    public function renderBodyEnd(&$output)
    {
        if(!isset($this->scriptFiles[self::POS_END]) && !isset($this->scripts[self::POS_END])
            && !isset($this->scripts[self::POS_READY]) && !isset($this->scripts[self::POS_LOAD]))
            return;

        $fullPage=0;
        $output=preg_replace('/(<\\/body\s*>)/is','<###end###>$1',$output,1,$fullPage);
        $html='';
        if(isset($this->scriptFiles[self::POS_END]))
        {
            foreach($this->scriptFiles[self::POS_END] as $scriptFile)
                $html.=CHtml::scriptFile($scriptFile['url'])."\n";
        }
        $scripts=isset($this->scripts[self::POS_END]) ? $this->scripts[self::POS_END] : array();
        if(isset($this->scripts[self::POS_READY]))
        {
            if($fullPage)
                $scripts[]="jQuery(function($) {\n".implode("\n",$this->scripts[self::POS_READY])."\n});";
            else
                $scripts[]=implode("\n",$this->scripts[self::POS_READY]);
        }
        if(isset($this->scripts[self::POS_LOAD]))
        {
            if($fullPage)
                $scripts[]="jQuery(window).load(function() {\n".implode("\n",$this->scripts[self::POS_LOAD])."\n});";
            else
                $scripts[]=implode("\n",$this->scripts[self::POS_LOAD]);
        }
        if(!empty($scripts))
            $html.=CHtml::script(implode("\n",$scripts))."\n";

        if($fullPage)
            $output=str_replace('<###end###>',$html,$output);
        else
            $output=$output.$html;
    }

    /**
     * Registers a CSS file
     * @param string $url URL of the CSS file
     * @param string $media media that the CSS file should be applied to. If empty, it means all media types.
     * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
     */
    public function registerCssFile($url,$media='',$priority = null)
    {
        //if (!stristr("assets", $url)) {
            if (!file_exists(Yii::app()->basePath .'/../'. $url)) return;

            $hash = hash_file('md5', Yii::app()->basePath .'/../'. $url);

            $criteria = new CDbCriteria();
            $criteria->limit = 3;
            $criteria->order = 'version DESC';
            $criteria->condition = 'scriptname = :name';
            $criteria->params = array(':name' => $url);

            $mgr = StaticManagerFileVersion::model()->findAll($criteria);
            if (!$mgr) {
                $mgr = new StaticManagerFileVersion();
                $mgr->scriptname = $url;
                $mgr->hash = $hash;
                $mgr->version = 1;
                $mgr->save();

                $v = 1;
            }
            else {
                $found = false;

                foreach ($mgr as $filever) {
                    if ($filever->hash == $hash) {
                        $found = true;
                        $v = $filever->version;
                    }
                }

                if (!$found) {
                    $mgri = new StaticManagerFileVersion();
                    $mgri->scriptname = $url;
                    $mgri->hash = $hash;
                    $mgri->version = intval((is_array($mgr)) ? $mgr[0]->version : $mgr->version) + 1;
                    $mgri->save();

                    $v = $mgri->version;
                }
            }
        //}
        //else $v = 1;

        $this->hasScripts=true;
        $this->cssFiles[] = array('url' => $url .'?v='. $v, 'media' => $media, 'priority' => $priority);
        $params=func_get_args();
        $this->recordCachingAction('clientScript','registerCssFile',$params);
        return $this;
    }

    /**
     * Registers a javascript file.
     * @param string $url URL of the javascript file
     * @param integer $position the position of the JavaScript code. Valid values include the following:
     * <ul>
     * <li>CClientScript::POS_HEAD : the script is inserted in the head section right before the title element.</li>
     * <li>CClientScript::POS_BEGIN : the script is inserted at the beginning of the body section.</li>
     * <li>CClientScript::POS_END : the script is inserted at the end of the body section.</li>
     * </ul>
     * @param string $priority the priority of script file. Syntax:
     * before {scriptname}, after {scriptname}
     * {scriptname} - any name of script file, including only part
     * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
     */
    public function registerScriptFile($url, $position = null, $priority = null)
    {
            if (!file_exists(Yii::app()->basePath .'/../'. $url)) {
              echo '<!-- not found '. $url .'-->';
              return;
            }

            $hash = hash_file('md5', Yii::app()->basePath .'/../'. $url);

            $criteria = new CDbCriteria();
            $criteria->limit = 3;
            $criteria->order = 'version DESC';
            $criteria->condition = 'scriptname = :name';
            $criteria->params = array(':name' => $url);

            $mgr = StaticManagerFileVersion::model()->findAll($criteria);
            if (!$mgr) {
                $mgr = new StaticManagerFileVersion();
                $mgr->scriptname = $url;
                $mgr->hash = $hash;
                $mgr->version = 1;
                $mgr->save();

                $v = 1;
            }
            else {
                $found = false;

                foreach ($mgr as $filever) {
                    if ($filever->hash == $hash) {
                        $found = true;
                        $v = $filever->version;
                    }
                }

                if (!$found) {
                    $mgri = new StaticManagerFileVersion();
                    $mgri->scriptname = $url;
                    $mgri->hash = $hash;
                    $mgri->version = intval($mgr[0]->version) + 1;
                    $mgri->save();

                    $v = $mgri->version;
                }
            }
        //}
        //else $v = 1;

        if($position===null)
            $position=$this->defaultScriptFilePosition;
        $this->hasScripts=true;
        $this->scriptFiles[$position][] = array('url' => $url .'?v='. $v, 'priority' => $priority);
        $params=func_get_args();
        $this->recordCachingAction('clientScript','registerScriptFile',$params);
        return $this;
    }
}