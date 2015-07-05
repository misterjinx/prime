<?php

namespace Prime\Router\Route;

use Prime\Router\Route\Simple;

class Complex extends Simple
{    
    public function match($path)
    {
        // start fresh
        $this->clearMatches();

        $parts = array_filter(explode('/', $path));
        if (count($parts) != count($this->parts)) {
            return false;
        }

        foreach ($parts as $key => $value) {
            $part = $this->parts[$key];

            // unnamed parameter
            if (strpos($part, '{') === false && strpos($part, '}') === false) {
                if ($part !== $value) {
                    return false; 
                }
            } else {
                // it is a named parameter match
                // check if there are multiple pairs of named parameters or not
                $bracketOpenCnt = substr_count($part, '{');
                $bracketCloseCnt = substr_count($part, '}');

                if ($bracketOpenCnt != $bracketCloseCnt) {
                    return false; // there is an error in named parameter
                } else {
                    // perhaps there is only one named parameter 
                    // and the whole part consists of the named parameter 
                    if ($bracketOpenCnt == 1 && $part[0] == '{' && $part[strlen($part) -1] == '}') {
                        $partName = trim($part, '{}');

                        // check if there is a filter for this parameter or not
                        if (isset($this->filters[$partName])) {
                            $pattern = $this->filters[$partName];
                            if (preg_match('#^'.$pattern.'$#', $value)) {
                                // value is ok, still a match
                                $this->matches[$partName] = $value;
                            } else {
                                // value doesnt match filter
                                return false;
                            }
                        } else {
                            // we don't care if there is no filter set
                            $this->matches[$partName] = $value;
                        }
                    } else {
                        // multiple named parameters inside part or the named 
                        // parameter is not the whole part
                        
                        // start search for named parameters inside part
                        $namedParams = array();
                        $regexPattern = '';
                        $found = -1; // no named params found at the beginning
                        $insideNamedParam = false;
                        for ($i = 0; $i < strlen($part); $i++) {
                            if ($part[$i] == '{') {
                                // start of named param
                                
                                if ($insideNamedParam) {
                                    // interleaved brackets, bad part, don't match
                                    return false;
                                }
                                                            
                                $found++;
                                if (isset($namedParams[$found])) {
                                    $namedParams[$found] .= $part[$i+1];    
                                } else {
                                    $namedParams[$found] = $part[$i+1];
                                }
                                
                                $insideNamedParam = true;
                                $i++;
                            } elseif ($part[$i] == '}') {
                                // end of named param
                                if (!$insideNamedParam) {
                                    // interleaved brackets, bad part, don't match
                                    return false;
                                }

                                // add the named param to the regex pattern
                                $partName = $namedParams[$found];
                                if (isset($this->filters[$partName])) {
                                    $partPattern = $this->filters[$partName];
                                } else {
                                    // default pattern
                                    $partPattern = '[a-zA-Z0-9_-]+';
                                }

                                $regexPattern .= '(?P<'.$namedParams[$found].'>' . $partPattern.')';

                                // reset inside param and continue
                                $insideNamedParam = false;
                                continue;
                            } else {
                                // regular character
                                // if inside named param, add it to param name
                                if ($insideNamedParam) {
                                    $namedParams[$found] .= $part[$i];
                                } else {
                                    // add it to the regex pattern as it is
                                    $regexPattern .= (in_array($part[$i], array('.', '-')) ? '\\' : '' ) . $part[$i];
                                }
                            }
                        }

                        if (preg_match('#^'.$regexPattern.'$#', $value, $partMatches)) {
                            // add named params matches to the list
                            foreach ($namedParams as $named_param) {                                
                                $this->matches[$named_param] = $partMatches[$named_param];
                            }
                        } else {
                            return false;
                        }
                    }
                } 
            }
        }

        return true;
    }    
}
