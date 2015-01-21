<?php
/**
 * Compiled source built from Loco core. Do not edit!
 * Wed, 21 Jan 2015 14:04:24 +0000
 */
class LocoArray implements Iterator, Countable { private $i; private $length; private $keys; private $vals; private $index; public function __construct( array $data = array() ){ $this->keys = array_keys( $data ); $this->vals = array_values( $data ); $this->index = array_flip( array_map( 'strtolower', $this->keys ) ); $this->length = count( $this->keys ); $this->rewind(); } public function add( $key, $val ){ $k = strtolower($key); if( isset($this->index[$k]) ){ $this->keys[ $this->index[$k] ] = $key; $this->vals[ $this->index[$k] ] = $val; } else { $this->index[$k] = $this->length++; $this->keys[] = $key; $this->vals[] = $val; } return $this; } public function to_array(){ return array_combine( $this->keys, $this->vals ); } function __toString(){ $pairs = array(); foreach( $this->keys as $i => $key ){ $pairs[] = trim($key).': '.$this->vals[$i]; } return implode("\n", $pairs ); } public function count(){ return $this->length; } public function trimmed( $prop ){ return trim( $this->__get($prop) ); } public function has( $key ){ return isset( $this->index[ strtolower($key) ] ); } public function __get( $key ){ $key = strtolower( $key ); return isset($this->index[$key]) ? $this->vals[ $this->index[$key] ] : null; } public function rewind(){ $this->i = reset( $this->index ); } public function key(){ return is_int($this->i) ? $this->keys[$this->i] : null; } public function current(){ return is_int($this->i) ? $this->vals[$this->i] : null; } public function valid(){ return is_int($this->i); } public function next(){ $this->i = next( $this->index ); } }  function loco_sniff_printf( $str ){ return (bool) preg_match('/%(?:\d\$)?(?:\+|-)?[ 0]?-?\d*(?:\.\d+)?[bcdeEfFgGosuxX]/', $str ); }   define( 'LOCO_FLAG_OK', 0 ); define( 'LOCO_FLAG_ERROR', 1 ); define( 'LOCO_FLAG_REVIEW', 2 ); define( 'LOCO_FLAG_PENDING', 3 ); define( 'LOCO_FLAG_FUZZY', 4 ); function loco_flags(){ static $flags = array ( LOCO_FLAG_OK => 'Translated', LOCO_FLAG_ERROR => 'Incorrect', LOCO_FLAG_REVIEW => 'Needs review', LOCO_FLAG_PENDING => 'Pending approval', LOCO_FLAG_FUZZY => 'Fuzzy', ); return $flags; } function loco_flag( $f ){ if( -1 === $f ){ return 'Untranslated'; } $flags = loco_flags(); if( ! isset($flags[$f]) ){ throw new Exception('Invalid LOCO_FLAG_ constant ', var_export($f,1) ); } return $flags[$f]; } function loco_flag_integer( $f ){ if( is_numeric($f) ){ $f = (int) $f; } else { $f = 'LOCO_FLAG_'.strtoupper($f); $f = defined($f) ? constant($f) : -1; } $flags = loco_flags(); if( ! isset($flags[$f]) ){ throw new InvalidArgumentException('Unknown flag, '.var_export(func_get_arg(0),1)); } return $f; } function loco_po_flags(){ static $map = array ( LOCO_FLAG_FUZZY => '#, fuzzy', ); return $map; }  function loco_parse_reference_id( $refs, &$_id ){ if( false === ( $n = strpos($refs,'loco:') ) ){ $_id = ''; return $refs; } $_id = substr($refs, $n+5, 24 ); $refs = substr_replace( $refs, '', $n, 29 ); return trim( $refs ); }  function loco_ensure_utf8( $str, $enc = false, $prefix_bom = false ){ if( false === $enc ){ $m = substr( $str, 0, 3 ); if( "\xEF\xBB\xBF" === $m ){ $str = substr( $str, 3 ); } else if( "\xFF" === $m{0} && "\xFE" === $m{1} ){ $str = substr( $str, 2 ); $enc = 'UTF-16LE'; } else if( "\xFE" === $m{0} && "\xFF" === $m{1} ){ $str = substr( $str, 2 ); $enc = 'UTF-16BE'; } else { $enc = mb_detect_encoding( $str, array('ASCII','UTF-8','Windows-1252','ISO-8859-1'), false ); if( ! $enc ){ throw new Exception('Unknown character encoding'); } } } else if( ! strcasecmp('ISO-8859-1',$enc) || ! strcasecmp('CP-1252', $enc ) ){ $enc = 'Windows-1252'; } else if( ! strcasecmp('UTF8', $enc) ){ $enc = ''; } if( $enc && $enc !== 'ASCII' && $enc !== 'UTF-8' ){ $str = iconv( $enc, 'UTF-8//TRANSLIT', $str ); if( ! $str ){ throw new Exception('Failed to ensure UTF-8 from '.$enc); } } if( $prefix_bom ){ $str = "\xEF\xBB\xBF".$str; } return $str; } function loco_parse_po( $src ){ $src = loco_ensure_utf8( $src ); $i = -1; $key = ''; $entries = array(); $template = array( '#' => array(), 'id' => array(), 'str' => array(), 'ctxt' => array() ); foreach( preg_split('/[\r\n]+/', $src) as $line ){ while( $line = trim($line) ){ $c = $line{0}; if( '"' === $c ){ if( $key && isset($entry) ){ $entry[$key][$idx][] = loco_po_unquote( $line ); } } else if( '#' === $c ){ if( isset($entry['i']) ){ unset( $entry ); $entry = $template; } $f = empty($line{1}) ? ' ' : $line{1}; $entry['#'][$f][] = trim( substr( $line, 1+strlen($f) ), "/ \n\r\t" ); } else if( preg_match('/^msg(id|str|ctxt|id_plural)(?:\[(\d+)\])?[ \t]+/', $line, $r ) ){ $key = $r[1]; $idx = isset($r[2]) ? (int) $r[2] : 0; if( 'str' === $key ){ if( ! isset($entry['i']) ){ $entry['i'] = ++$i; $entries[$i] = &$entry; } } else if( ! isset($entry) || isset($entry['i']) ){ unset( $entry ); $entry = $template; } $line = substr( $line, strlen($r[0]) ); continue; } continue 2; } } unset( $entry ); $assets = array(); foreach( $entries as $i => $entry ){ if( empty($entry['id']) ){ continue; } if( empty($entry['str']) ){ $entry['str'] = array( array('') ); } $asset = array ( 'id' => '', 'source' => implode('',$entry['id'][0]), 'target' => implode('',$entry['str'][0]), ); $parse_printf = true; if( isset($entry['ctxt'][0]) ){ $asset['context'] = implode('',$entry['ctxt'][0]); } if( isset($entry['#'][' ']) ){ $asset['comment'] = implode("\n", $entry['#'][' '] ); } if( isset($entry['#']['.']) ){ $asset['notes'] = implode("\n", $entry['#']['.'] ); } if( isset($entry['#'][':']) ){ if( $refs = implode( "\n", $entry['#'][':'] ) ) { if( $refs = loco_parse_reference_id( $refs, $_id ) ){ $asset['refs'] = $refs; } if( $_id ){ $asset['_id'] = $_id; } } } if( isset($entry['#'][',']) ){ foreach( $entry['#'][','] as $flag ){ if( preg_match('/((?:no-)?\w+)-format/', $flag, $r ) ){ $parse_printf = false; if( 'no-' === substr($r[1],0,3) ){ $asset['format'] = false; } else { $asset['format'] = $r[1]; } } else if( $flag = loco_po_parse_flag($flag) ){ $asset['flag'] = $flag; break; } } } if( $parse_printf ){ if( $asset['source'] && loco_sniff_printf( $asset['source'] ) ){ $asset['format'] = 'c'; $parse_printf = false; } } $pidx = count($assets); $assets[] = $asset; if( isset($entry['id_plural']) || isset($entry['str'][1]) ){ $idx = 0; $num = max( 2, count($entry['str']) ); while( ++$idx < $num ){ $plural = array ( 'id' => '', 'source' => '', 'target' => isset($entry['str'][$idx]) ? implode('',$entry['str'][$idx]) : '', 'plural' => $idx, 'parent' => $pidx, ); if( 1 === $idx ){ $plural['source'] = isset($entry['id_plural'][0]) ? implode('',$entry['id_plural'][0]) : ''; } if( $parse_printf ){ if( $plural['source'] && loco_sniff_printf( $plural['source'] ) ){ $assets[$pidx]['format'] = 'c'; $parse_printf = false; } } $assets[] = $plural; } } } if( $assets && '' === $assets[0]['source'] ){ $headers = loco_parse_po_headers( $assets[0]['target'] ); $indexed = $headers->__get('X-Loco-Lookup'); if( 'id' === $indexed || 'name' === $indexed ){ foreach( $assets as $i => $asset ){ if( isset($asset['notes']) ){ $notes = $texts = array(); foreach( explode("\n",$asset['notes']) as $line ){ 0 === strpos($line,'Source text: ') ? $texts[] = substr($line,13) : $notes[] = $line; } $assets[$i]['notes'] = implode("\n",$notes); $assets[$i]['id'] = $asset['source']; $assets[$i]['source'] = implode("\n",$texts); } } } } return $assets; } function loco_po_parse_flag( $text, $flag = 0 ){ $map = loco_po_flags(); foreach( explode(',',$text) as $needle ){ if( $needle = trim($needle) ){ foreach( $map as $loco_flag => $haystack ){ if( false !== stripos($haystack, $needle) ){ $flag |= $loco_flag; break; } } } } return $flag; } function loco_po_unquote( $str ){ return substr( stripcslashes($str), 1, -1 ); } function loco_parse_po_headers( $str ){ $headers = new LocoArray( array() ); foreach( explode("\n",$str) as $line ){ $i = strpos($line,':') and $key = trim( substr($line,0,$i) ) and $headers->add( $key, trim( substr($line,++$i) ) ); } return $headers; }  class LocoMoParser { private $bin; private $be; private $n; private $o; private $t; private $v; public function __construct( $bin ){ $this->bin = $bin; } public function getAt( $idx ){ $offset = $this->targetOffset(); $offset += ( $idx * 8 ); $len = $this->integerAt( $offset ); $idx = $this->integerAt( $offset + 4 ); $txt = $this->bytes( $idx, $len ); if( false === strpos( $txt, "\0") ){ return $txt; } return explode( "\0", $txt ); } public function parse(){ $sourceOffset = $this->sourceOffset(); $targetOffset = $this->targetOffset(); $r = array(); $p = array(); $i = 0; $offset = $sourceOffset; while( $offset < $targetOffset ){ $r[$i] = array( 'id' => '', 'source' => '', 'target' => '' ); $len = $this->integerAt( $offset ); $idx = $this->integerAt( $offset + 4 ); $src = $this->bytes( $idx, $len ); $eot = strpos( $src, "\x04" ); if( false !== $eot ){ $r[$i]['context'] = $this->decodeStr( substr($src, 0, $eot ) ); $src = substr( $src, $eot+1 ); } $nul = strpos( $src, "\0" ); if( false !== $nul ){ $p[$i][1] = array( 'id' => '', 'source' => substr($src, $nul+1 ), 'target' => '', 'parent' => $i, 'plural' => 1 ); $src = substr( $src, 0, $nul ); } $r[$i++]['source'] = $this->decodeStr($src); $offset += 8; } $t = $i; $offset = $targetOffset; for( $i = 0; $i < $t; $i++ ){ $len = $this->integerAt( $offset ); $idx = $this->integerAt( $offset + 4 ); $txt = $this->bytes( $idx, $len ); if( false !== strpos( $txt, "\0" ) ){ $arr = explode( "\0", $txt ); $txt = array_shift( $arr ); if( isset($p[$i][1]) ){ foreach( $arr as $_i => $plural_txt ){ $plural_idx = $_i + 1; $p[$i][$plural_idx]['target'] = $this->decodeStr($plural_txt); } } else if( '' === implode('',$arr) ){ } else { throw new Exception('plural has no corresponding msgid_plural at '.$i); } } $r[$i]['target'] = $this->decodeStr($txt); $offset += 8; } foreach( $p as $parent_id => $plurals ){ foreach( $plurals as $plural_idx => $msg ){ if( 1 < $plural_idx ){ $msg['source'] = $plurals[1]['source'].' (plural '.$plural_idx.')'; } $msg['parent'] = $parent_id; $msg['plural'] = $plural_idx; $r[] = $msg; } } return $r; } public function isBigendian(){ while( is_null($this->be) ){ $str = $this->words( 0, 2 ); $arr = unpack('V', $str); if( 0x950412de === $arr[1] ){ $this->be = false; break; } if( 0xde120495 === $arr[1] ){ $this->be = true; break; } throw new Exception('Invalid MO format'); } return $this->be; } public function version(){ if( is_null($this->v) ){ $this->v = $this->integerWord(1); } return $this->v; } public function count(){ if( is_null($this->n) ){ $this->n = $this->integerWord(2); } return $this->n; } public function sourceOffset(){ if( is_null($this->o) ){ $this->o = $this->integerWord(3); } return $this->o; } public function targetOffset(){ if( is_null($this->t) ){ $this->t = $this->integerWord(4); } return $this->t; } public function getHashTable(){ $s = $this->integerWord(5); $h = $this->integerWord(6); return $this->bytes( $h, $s * 4 ); } private function bytes( $offset, $length ){ return substr( $this->bin, $offset, $length ); } private function words( $offset, $length ){ return $this->bytes( $offset * 4, $length * 4 ); } private function integerWord( $offset ){ return $this->integerAt( $offset * 4 ); } private function integerAt( $offset ){ $str = $this->bytes( $offset, 4 ); $fmt = $this->isBigendian() ? 'N' : 'V'; $arr = unpack( $fmt, $str ); if( ! isset($arr[1]) || ! is_int($arr[1]) ){ throw new Exception('Failed to read 32 bit integer at byte '.$offset); } return $arr[1]; } private function decodeStr( $str ){ $enc = mb_detect_encoding( $str, array('ASCII','UTF-8','ISO-8859-1'), false ); if( $enc && $enc !== 'ASCII' && $enc !== 'UTF-8' ){ $str = iconv( $enc, 'UTF-8', $str ); } return $str; } } function loco_parse_mo( $src ){ $mo = new LocoMoParser($src); return $mo->parse(); }    final class PHPTokens implements Iterator { private $tokens; private $i; private $skip_tokens = array(); private $skip_strings = array(); private $literal_tokens = array(); public function __construct( array $tokens ){ $this->tokens = $tokens; $this->rewind(); } public function literal(){ foreach( func_get_args() as $t ){ $this->literal_tokens[ $t ] = 1; } return $this; } public function ignore(){ foreach( func_get_args() as $t ){ if( is_int($t) ){ $this->skip_tokens[$t] = true; } else { $this->skip_strings[$t] = true; } } return $this; } public function export(){ $arr = array(); foreach( $this as $tok ){ $arr[] = $tok; } return $arr; } public function advance(){ $this->next(); return $this->current(); } public function pop(){ $tok = array_pop( $this->tokens ); $this->rewind(); return $tok; } public function shift(){ $tok = array_shift( $this->tokens); $this->rewind(); return $tok; } public function rewind(){ $this->i = ( false === reset($this->tokens) ? null : key($this->tokens) ); } public function valid(){ while( isset($this->i) ){ $tok = $this->tokens[$this->i]; if( is_array($tok) ){ if( isset($this->skip_tokens[$tok[0]]) ){ $this->next(); } else { return true; } } else if( isset($this->skip_strings[$tok]) ){ $this->next(); } else { return true; } } return false; } public function key(){ return $this->i; } public function next(){ $this->i = ( false === next($this->tokens) ? null : key($this->tokens) ); } public function current(){ if( ! $this->valid() ){ return false; } $tok = $this->tokens[$this->i]; if( is_array($tok) && isset($this->literal_tokens[$tok[0]]) ){ return $tok[1]; } return $tok; } public function __toString(){ $s = ''; foreach( $this as $token ){ $s .= is_array($token) ? $token[1] : $token; } return $s; } }  function loco_parse_comment($comment){ if( '*' === $comment{1} ){ $lines = array(); foreach( explode("\n", $comment) as $line ){ $line and $lines[] = trim($line,"/* \r\t"); } $comment = implode("\n", $lines); } return trim( $comment,"/ \n\r\t" ); }   function decapse_string( $s ){ if( empty($s) ){ return ''; } $q = $s{0}; switch( $q ){ case "'": case '"': $s = substr( $s, 1, -1 ); if( '"' === $q ){ return stripcslashes($s); } return str_replace( array( '\\'.$q, '\\\\' ), array( $q, '\\' ), $s ); default: return $s; } } function loco_extract_php( array $tokens, $fileref = '' ){ $extractor = new LocoPHPExtractor; return $extractor->extract( $tokens, $fileref ); } final class LocoPHPExtractor { private static $rules = array ( '_' => 's', 'gettext' => 's', 'dgettext' => '_s', 'ngettext' => 'sp', 'dngettext' => '_sp', '__' => 's', '_e' => 's', '_c' => 's', '_n' => 'sp', '_n_noop' => 'sp', '_nc' => 'sp', '__ngettext' => 'sp', '__ngettext_noop' => 'sp', '_x' => 'sc', '_ex' => 'sc', '_nx' => 'sp_c', '_nx_noop' => 'spc', '_n_js' => 'sp', '_nx_js' => 'spc', 'esc_attr__' => 's', 'esc_html__' => 's', 'esc_attr_e' => 's', 'esc_html_e' => 's', 'esc_attr_x' => 'sc', 'esc_html_x' => 'sc', 'comments_number_link' => '_sp', 't' => 's', 'st' => 's', 'trans' => 's', 'transChoice' => 'sp', ); private $exp = array(); private $reg = array(); public function extract( array $tokens, $fileref = '' ){ $tokens = new PHPTokens( $tokens ); $tokens->ignore( T_WHITESPACE ); $comment = ''; foreach( $tokens as $tok ){ if( isset($args) ){ if( ')' === $tok ){ isset($arg) and $arg and $args[] = $arg; $this->push( $rule, $args, $comment, $ref ); unset($args,$arg); $comment = ''; } else if( ',' === $tok ){ isset($arg) and $arg and $args[] = $arg; unset($arg); } else if( isset($arg) ){ $arg[] = $tok; } else { $arg = array( $tok ); } } else if( is_array($tok) ){ list($t,$s) = $tok; if( T_COMMENT === $t || T_DOC_COMMENT === $t ){ $comment = $s; } else if( T_STRING === $t && isset(self::$rules[$s]) && '(' === $tokens->advance() ){ $rule = self::$rules[$s]; $args = array(); $ref = $fileref ? $fileref.':'.$tok[2]: ''; } else if( $comment && false === stripos($comment, 'translators') ){ $comment = ''; } } } return $this->exp; } private function push( $rule, array $args, $comment = '', $ref = '' ){ $s = strpos( $rule, 's'); $p = strpos( $rule, 'p'); $c = strpos( $rule, 'c'); foreach( $args as $i => $tokens ){ if( 1 === count($tokens) && is_array($tokens[0]) && T_CONSTANT_ENCAPSED_STRING === $tokens[0][0] ){ $args[$i] = decapse_string( $tokens[0][1] ); } else { $args[$i] = null; } } $key = $msgid = $args[$s]; if( ! $msgid ){ return null; } $entry = array ( 'id' => '', 'source' => $msgid, 'target' => '', ); if( $c && isset($args[$c]) ){ $entry['context'] = $args[$c]; $key .= "\0". $args[$c]; } if( $ref ){ $entry['refs'] = $ref; } $parse_printf = true; if( $comment ){ if( preg_match('/xgettext:\s*((?:no-)?\w+)-format/', $comment, $r ) ){ if( 'no-' === substr($r[1],0,3) ){ $entry['format'] = false; } else { $entry['format'] = $r[1]; } $comment = str_replace( $r[0], '', $comment ); $parse_printf = false; } $entry['notes'] = loco_parse_comment($comment); } if( $parse_printf && loco_sniff_printf( $msgid ) ){ $entry['format'] = 'php'; $parse_printf = false; } if( isset($this->reg[$key]) ){ $index = $this->reg[$key]; $a = array(); isset($this->exp[$index]['refs']) and $a[] = $this->exp[$index]['refs']; isset($entry['refs']) and $a[] = $entry['refs']; $a && $this->exp[$index]['refs'] = implode(" ", $a ); $a = array(); isset($this->exp[$index]['notes']) and $a[] = $this->exp[$index]['notes']; isset($entry['notes']) and $a[] = $entry['notes']; $a && $this->exp[$index]['notes'] = implode("\n", $a ); } else { $index = count($this->exp); $this->reg[$key] = $index; $this->exp[] = $entry; } if( $p && isset($args[$p]) ){ $msgid_plural = $args[$p]; $entry = array ( 'id' => '', 'source' => $msgid_plural, 'target' => '', 'plural' => 1, 'parent' => $index, ); if( $parse_printf && loco_sniff_printf( $msgid_plural ) ){ $this->exp[$index]['format'] = 'php'; } $key = $msgid_plural."\0\0"; if( isset($this->reg[$key]) ){ $plural_index = $this->reg[$key]; $this->exp[$plural_index] = $entry; } else { $plural_index = count($this->exp); $this->reg[$key] = $plural_index; $this->exp[] = $entry; } } return $index; } public function get_xgettext( $input = '-' ){ $cmd = defined('WHICH_XGETTEXT') ? WHICH_XGETTEXT : 'xgettext'; $cmd.= ' -LPHP -c -o-'; if( $k = $this->get_xgettext_keywords() ){ $cmd.= ' -k'.implode(' -k', $k ); } return $cmd.' '.$input; } public function get_xgettext_keywords(){ $ks = array(); foreach( self::$rules as $word => $rule ){ $s = strpos( $rule, 's'); $k = $word.':'.++$s; if( false !== $p = strpos( $rule, 'p') ){ $k.= ','.++$p; } if( false !== $p = strpos( $rule, 'c') ){ $k.= ','.++$p.'c'; } $ks[] = $k; } return $ks; } }  function loco_relative_path( $source_path, $target_path ){ $rel = ''; $common = false; $src = preg_split('!/+!', $source_path, -1, PREG_SPLIT_NO_EMPTY ); $dst = preg_split('!/+!', $target_path, -1, PREG_SPLIT_NO_EMPTY ); while( $src && $dst ){ if( current($src) !== current($dst) ){ break; } $common = true; array_shift($src); array_shift($dst); } if( ! $common ){ return $target_path; } if( $src ){ $up = array_fill( 0, count($src), '..' ); $rel = implode('/', $up ); } if( $dst ){ $rel && $rel .= '/'; $rel .= implode('/', $dst ); } return $rel; }    class LocoMo { private $bin; private $msgs; private $head; private $hash; public function __construct( array $export, LocoArray $head = null ){ if( ! $head ){ $head = new LocoArray( array( 'Project-Id-Version' => 'Loco', 'Language' => 'English', 'Plural-Forms' => 'nplurals=2; plural=(n!=1);', 'MIME-Version' => '1.0', 'Content-Type' => 'text/plain; charset=UTF-8', 'Content-Transfer-Encoding' => '8bit', 'X-Generator' => 'Loco '.PLUG_HTTP_ADDR, ) ); } $this->head = $head; $this->msgs = $export; $this->bin = ''; } public function enableHash(){ return $this->hash = new LocoMoTable; } public function setHeader( $key, $val ){ $this->head->add($key, $val); return $this; } public function setProject( LocoProject $Proj ){ return $this ->setHeader( 'Project-Id-Version', $Proj->proj_name ) ->setHeader($key, $val) ; } public function setLocale( LocoProjectLocale $Loc ){ return $this ->setHeader( 'Language', $Loc->label ) ->setHeader( 'Plural-Forms', 'nplurals='.$Loc->nplurals.'; plural='.$Loc->pluraleq.';' ) ; } public function count(){ return count($this->msgs); } public function compile(){ $table = array(''); $sources = array(''); $targets = array( $this->head->__toString() ); foreach( $this->msgs as $r ){ $msgid = $r['key']; if( isset($r['context']) && $r['context'] ){ $msgid or $msgid = "(".$r['context'].')'; $msgid = $r['context']."\x04".$msgid; } if( ! $msgid ){ continue; } $msgstr = $r['translation']; if( ! $msgstr ){ continue; } $table[] = $msgid; if( isset($r['plurals']) ){ foreach( $r['plurals'] as $i => $p ){ if( $i === 0 ){ $msgid .= "\0".$p['key']; } $msgstr .= "\0".$p['translation']; } } $sources[] = $msgid; $targets[] = $msgstr; } asort( $sources, SORT_STRING ); $this->bin = ''; $this->writeInteger( 0x950412de ); $this->writeInteger( 0 ); $n = count($sources); $this->writeInteger( $n ); $offset = 28; $this->writeInteger( $offset ); $offset += $n * 8; $this->writeInteger( $offset ); if( $this->hash ){ sort( $table, SORT_STRING ); $this->hash->compile( $table ); $s = $this->hash->count(); } else { $s = 0; } $this->writeInteger( $s ); $offset += $n * 8; $this->writeInteger( $offset ); if( $s ){ $offset += $s * 4; } $source = ''; foreach( $sources as $i => $str ){ $source .= $str."\0"; $this->writeInteger( $strlen = strlen($str) ); $this->writeInteger( $offset ); $offset += $strlen + 1; } $target = ''; foreach( array_keys($sources) as $i ){ $str = $targets[$i]; $target .= $str."\0"; $this->writeInteger( $strlen = strlen($str) ); $this->writeInteger( $offset ); $offset += $strlen + 1; } if( $this->hash ){ $this->bin .= $this->hash->__toString(); } $this->bin .= $source; $this->bin .= $target; return $this->bin; } private function writeInteger( $num ){ $this->bin .= pack( 'V', $num ); return $this; } }  class LocoMoTable { private $size = 0; private $bin = ''; private $map; public function __construct( $data = null ){ if( is_array($data) ){ $this->compile( $data ); } else if( $data ){ $this->parse( $data ); } } public function count(){ if( ! isset($this->size) ){ if( $this->bin ){ $this->size = (int) ( strlen( $this->bin ) / 4 ); } else if( is_array($this->map) ){ $this->size = count($this->map); } else { return 0; } if( ! self::is_prime($this->size) || $this->size < 3 ){ throw new Exception('Size expected to be prime number above 2, got '.$this->size); } } return $this->size; } public function bytes(){ return $this->count() * 4; } public function __toString(){ return $this->bin; } public function export(){ if( ! is_array($this->map) ){ $this->parse( $this->bin ); } return $this->map; } private function reset( $length ){ $this->size = max( 3, self::next_prime ( $length * 4 / 3 ) ); $this->bin = null; $this->map = array(); return $this->size; } public function compile( array $msgids ){ $hash_tab_size = $this->reset( count($msgids) ); $packed = array_fill( 0, $hash_tab_size, "\0\0\0\0" ); $j = 0; foreach( $msgids as $msgid ){ $hash_val = self::hashpjw( $msgid ); $idx = $hash_val % $hash_tab_size; if( array_key_exists($idx, $this->map) ){ $incr = 1 + ( $hash_val % ( $hash_tab_size - 2 ) ); do { $idx += $incr; if( $hash_val === $idx ){ throw new Exception('Unable to find empty slot in hash table'); } $idx %= $hash_tab_size; } while( array_key_exists($idx, $this->map ) ); } $this->map[$idx] = $j; $packed[$idx] = pack('V', ++$j ); } return $this->bin = implode('',$packed); } public function lookup( $msgid, array $msgids ){ $hash_val = self::hashpjw( $msgid ); $idx = $hash_val % $this->size; $incr = 1 + ( $hash_val % ( $this->size - 2 ) ); while( true ){ if( ! array_key_exists($idx, $this->map) ){ break; } $j = $this->map[$idx]; if( isset($msgids[$j]) && $msgid === $msgids[$j] ){ return $j; } $idx += $incr; if( $idx === $hash_val ){ break; } $idx %= $this->size; } return -1; } public function parse( $bin ){ $this->bin = (string) $bin; $this->size = null; $hash_tab_size = $this->count(); $this->map = array(); $idx = -1; $byte = 0; while( ++$idx < $hash_tab_size ){ $word = substr( $this->bin, $byte, 4 ); if( "\0\0\0\0" !== $word ){ list(,$j) = unpack('V', $word ); $this->map[$idx] = $j - 1; } $byte += 4; } return $this->map; } public static function hashpjw( $str ){ $i = -1; $hval = 0; $len = strlen($str); while( ++$i < $len ){ $ord = ord( $str{$i} ); $hval = ( $hval << 4 ) + $ord; $g = $hval & 0xf0000000; if( $g !== 0 ){ $hval ^= $g >> 24; $hval ^= $g; } } return $hval; } private static function next_prime( $seed ){ $seed |= 1; while ( ! self::is_prime($seed) ){ $seed += 2; } return $seed; } private static function is_prime( $num ) { if ($num === 1 ){ return false; } if( $num === 2 ){ return true; } if( $num % 2 == 0 ) { return false; } for( $i = 3; $i <= ceil(sqrt($num)); $i = $i + 2) { if($num % $i == 0 ){ return false; } } return true; } }  function loco_msgfmt( $po, $withhash = false ){ if( ! is_array($po) ){ $po = loco_parse_po( $po ); } $head = null; if( isset($po[0]) && '' === $po[0]['source'] ){ $head = loco_parse_po_headers( $po[0]['target'] ); $po[0] = null; } $export = array(); foreach( $po as $i => $r ){ if( ! $r ){ continue; } $msg = array ( 'key' => $r['source'], 'translation' => $r['target'], ); if( isset($r['parent']) ){ unset( $parent ); $parent = &$export[ $r['parent'] ]; isset($parent['plurals']) or $parent['plurals'] = array(); $parent['plurals'][] = $msg; } else { isset($r['context']) and $msg['context'] = $r['context']; $export[$i] = $msg; } } $mo = new LocoMo( $export, $head ); if( $withhash ){ $mo->enableHash(); } return $mo->compile(); } function loco_po_stats( array $po ){ $t = $n = $f = $u = 0; foreach( $po as $r ){ if( ! isset($r['source']) || '' === $r['source'] ){ continue; } if( isset($r['parent']) && is_int($r['parent']) ){ continue; } $t++; if( '' === $r['target'] ){ $u++; } else if( isset($r['flag']) && LOCO_FLAG_FUZZY === $r['flag'] ){ $f++; } else { $n++; } } $r = $t && $n ? $n / $t : 0; $p = (string) round( $r * 100 ); return compact('t','p','f','u'); } 