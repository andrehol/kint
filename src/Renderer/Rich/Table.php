<?php

class Kint_Renderer_Rich_Table extends Kint_Renderer_Rich_Plugin
{
    public static $respect_str_length = true;

    public function render($r)
    {
        $out = '<pre><table><thead><tr><th></th>';

        $firstrow = reset($r->contents);

        if (!is_object($firstrow->value)) {
            var_dump($r);
            exit;
        }

        foreach ($firstrow->value->contents as $column => $field) {
            $out .= '<th>'.Kint_Object_Blob::escape($column).'</th>';
        }

        $out .= '</tr></thead><tbody>';

        foreach ($r->contents as $row) {
            $out .= '<tr><th>';
            $out .= Kint_Object_Blob::escape($row->name);
            $out .= '</th>';

            foreach ($row->value->contents as $field) {
                $out .= '<td';
                $type = '';
                $size = '';

                if (($s = $field->getType()) !== null) {
                    $type = $s;

                    if (($s = $field->getSize()) !== null) {
                        $size .= '('.Kint_Object_Blob::escape($s).') ';
                    }
                }

                if ($type) {
                    $out .= ' title="'.$type.$size.'"';
                }

                $out .= '>';

                switch ($field->type) {
                    case 'boolean':
                        $out .= $field->value->contents ? '<var>true</var>' : '<var>false</var>';
                        break;
                    case 'integer':
                    case 'double':
                        $out .= (string) $field->value->contents;
                        break;
                    case 'null':
                        $out .= '<var>null</var>';
                        break;
                    case 'string':
                        $val = $field->value->contents;
                        if (Kint_Renderer_Rich::$strlen_max && self::$respect_str_length && Kint_Object_Blob::strlen($val) > Kint_Renderer_Rich::$strlen_max) {
                            $val = substr($val, 0, Kint_Renderer_Rich::$strlen_max).'...';
                        }

                        $out .= Kint_Object_Blob::escape($val);
                        break;
                    case 'array':
                        $out .= '<var>array</var>'.$size;
                        break;
                    case 'object':
                        $out .= '<var>'.Kint_Object_Blob::escape($field->classname).'</var>'.$size;
                        break;
                    case 'resource':
                        $out .= '<var>resource</var>';
                        break;
                    default:
                        $out .= '<var>unknown</var>';
                        break;
                }

                if (in_array('blacklist', $field->hints)) {
                    $out .= ' <var>Blacklisted</var>';
                } elseif (in_array('recursion', $field->hints)) {
                    $out .= ' <var>Recursion</var>';
                } elseif (in_array('depth_limit', $field->hints)) {
                    $out .= ' <var>Depth Limit</var>';
                }

                $out .= '</td>';
            }

            $out .= '</tr>';
        }

        $out .= '</tbody></table></pre>';

        return $out;
    }
}
