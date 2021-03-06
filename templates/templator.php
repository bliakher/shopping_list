<?php

/**
 * Represents the template compiler.
 */
class Templator
{
    private $file = null;
    /**
     * Load a template file into memory.
     * @param string $fileName Path to the template file to be loaded.
     */
    public function loadTemplate(string $fileName)
    {
        if(file_exists($fileName)) {
            $this->file = fopen($fileName, 'r');
        }
        else {
            throw new Exception("File $fileName not found.");
        }
    }

    /**
     * Compile the loaded template (transpill it into interleaved-PHP) and save the result in a file.
     * @param string $fileName Path where the result should be saved.
     */
    public function compileAndSave(string $fileName)
    {
        $clause_stack = [];
        $lambda = function ($matches) use (&$clause_stack) {
            $match = $matches[0];
            $pattern = "/^{(=|if|foreach|for) *}$/";
            if (preg_match($pattern, $match)) {
                throw new Exception("Missing expression in clause.");
            }
            $pattern = "/^{= (?<expr>[^{}]+)}$/";
            $found = [];
            if(preg_match($pattern, $match, $found)) {
                $expr = $found["expr"];
                return "<?= htmlspecialchars($expr) ?>";
            }
            $pattern = "/^{(?<cmd>if|foreach|for) (?<expr>[^{}]+)}$/";
            if(preg_match($pattern, $match, $found)) {
                $cmd = $found["cmd"];
                $expr = $found["expr"];
                array_push($clause_stack, $cmd);
                return "<?php $cmd ($expr) { ?>";
            }
            $pattern = "/^{\/(?<cmd>if|foreach|for)}$/";
            if(preg_match($pattern, $match, $found)) {
                $cmd = $found["cmd"];
                if (count($clause_stack) !== 0 and array_pop($clause_stack) == $cmd){
                    return "<?php } ?>";
                }
                else {
                    throw new Exception("Invalid closing marker, clause $cmd was not opened.");
                }
            }
            return $match;
        };
        $output = fopen($fileName, 'w');
        if (!$output) {
            throw new Exception("Invalid path $fileName.");
        }
        if ($this->file) {
            $line = fgets($this->file);
            while ($line !== false) {
                $pattern = "/{[^{}]+}/";
                $line = preg_replace_callback($pattern, $lambda, $line);
                fwrite($output, $line);
                $line = fgets($this->file);
            }
        }
        else{
            throw new Exception("Input file not opened.");
        }
        if(count($clause_stack) !== 0){
            throw new Exception("Unclosed statements left.");
        }
        fclose($output);
        fclose($this->file);
    }   

    
}
