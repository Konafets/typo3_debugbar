<?php namespace Konafets\Typo3Debugbar\Overrides;

use TYPO3\CMS\Core\TimeTracker\TimeTracker as BaseTimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Frontend Timetracking functions
 *
 * Is used to register how much time is used with operations in TypoScript
 */
class TimeTracker extends BaseTimeTracker
{
    /**
     * Print TypoScript parsing log
     *
     * @return string HTML table with the information about parsing times.
     */
    public function generateResults()
    {
        if (!$this->isEnabled) {
            return '';
        }

        // Calculate times and keys for the tsStackLog
        foreach ($this->tsStackLog as $uniqueId => &$data) {
            $data['endtime'] = $this->getDifferenceToStarttime($data['endtime']);
            $data['starttime'] = $this->getDifferenceToStarttime($data['starttime']);
            $data['deltatime'] = $data['endtime'] - $data['starttime'];
            if (is_array($data['tsStack'])) {
                $data['key'] = implode($data['stackPointer'] ? '.' : '/', end($data['tsStack']));
            }
        }

        unset($data);

        // Create hierarchical array of keys pointing to the stack
        $arr = [];
        foreach ($this->tsStackLog as $uniqueId => $data) {
            $this->createHierarchyArray($arr, $data['level'], $uniqueId);
        }

        // Parsing the registered content and create icon-html for the tree
        $this->tsStackLog[$arr['0.'][0]]['content'] = $this->fixContent($arr['0.'], $this->tsStackLog[$arr['0.'][0]]['content'], '', 0, $arr['0.'][0]);

        // Displaying the tree:
        $outputArr = [];
        $outputArr[] = $this->fw('TypoScript Key');
        $outputArr[] = $this->fw('Value');
        if ($this->printConf['allTime']) {
            $outputArr[] = $this->fw('Time');
            $outputArr[] = $this->fw('Own');
            $outputArr[] = $this->fw('Sub');
            $outputArr[] = $this->fw('Total');
        } else {
            $outputArr[] = $this->fw('Own');
        }
        $outputArr[] = $this->fw('Details');

        $out = '';
        $foo = ['headers'];
        foreach ($outputArr as $row) {
            $out .= '<th>' . $row . '</th>';
            $foo['headers'][] .= $row;
        }
        $out = '<thead><tr>' . $out . '</tr></thead>';
        $flag_tree = $this->printConf['flag_tree'];
        $flag_messages = $this->printConf['flag_messages'];
        $flag_content = $this->printConf['flag_content'];
        $keyLgd = $this->printConf['keyLgd'];
        $c = 0;
        foreach ($this->tsStackLog as $uniqueId => $data) {
            if ($this->highlightLongerThan && (int)$data['owntime'] > (int)$this->highlightLongerThan) {
                $logRowClass = 'typo3-debugbar-logRow-highlight';
            } else {
                $logRowClass = $c % 2 ? 'line-odd' : 'line-even';
            }
            $item = '';
            // If first...
            if (!$c) {
                $data['icons'] = '';
                $data['key'] = 'Script Start';
                $data['value'] = '';
            }
            // Key label:
            $keyLabel = '';
            if (!$flag_tree && $data['stackPointer']) {
                $temp = [];
                foreach ($data['tsStack'] as $k => $v) {
                    $temp[] = GeneralUtility::fixed_lgd_cs(implode($v, $k ? '.' : '/'), -$keyLgd);
                }
                array_pop($temp);
                $temp = array_reverse($temp);
                array_pop($temp);
                if (!empty($temp)) {
                    $keyLabel = '<br /><span style="color:#999999;">' . implode($temp, '<br />') . '</span>';
                }
            }
            if ($flag_tree) {
                $tmp = GeneralUtility::trimExplode('.', $data['key'], true);
                $theLabel = end($tmp);
            } else {
                $theLabel = $data['key'];
            }
            $theLabel = GeneralUtility::fixed_lgd_cs($theLabel, -$keyLgd);
            $theLabel = $data['stackPointer'] ? '<span class="stackPointer">' . $theLabel . '</span>' : $theLabel;
            $keyLabel = $theLabel . $keyLabel;
            $item .= '<td class="typo3-debugbar-table-cell-nowrap ' . $logRowClass . '">' . ($flag_tree ? $data['icons'] : '') . $this->fw($keyLabel) . '</td>';
            // Key value:
            $keyValue = $data['value'];
            $item .= '<td class="' . $logRowClass . ' typo3-debugbar-tsLogTime">' . $this->fw(htmlspecialchars($keyValue)) . '</td>';
            if ($this->printConf['allTime']) {
                $item .= '<td class="' . $logRowClass . ' typo3-debugbar-tsLogTime"> ' . $this->fw($data['starttime']) . '</td>';
                $item .= '<td class="' . $logRowClass . ' typo3-debugbar-tsLogTime"> ' . $this->fw($data['owntime']) . '</td>';
                $item .= '<td class="' . $logRowClass . ' typo3-debugbar-tsLogTime"> ' . $this->fw(($data['subtime'] ? '+' . $data['subtime'] : '')) . '</td>';
                $item .= '<td class="' . $logRowClass . ' typo3-debugbar-tsLogTime"> ' . $this->fw(($data['subtime'] ? '=' . $data['deltatime'] : '')) . '</td>';
            } else {
                $item .= '<td class="' . $logRowClass . ' typo3-debugbar-tsLogTime"> ' . $this->fw($data['owntime']) . '</td>';
            }
            // Messages:
            $msgArr = [];
            $msg = '';
            if ($flag_messages && is_array($data['message'])) {
                foreach ($data['message'] as $v) {
                    $msgArr[] = nl2br($v);
                }
            }
            if ($flag_content && (string)$data['content'] !== '') {
                $maxlen = 120;
                // Break lines which are too longer than $maxlen chars (can happen if content contains long paths...)
                if (preg_match_all('/(\\S{' . $maxlen . ',})/', $data['content'], $reg)) {
                    foreach ($reg[1] as $key => $match) {
                        $match = preg_replace('/(.{' . $maxlen . '})/', '$1 ', $match);
                        $data['content'] = str_replace($reg[0][$key], $match, $data['content']);
                    }
                }
                $msgArr[] = nl2br($data['content']);
            }
            if (!empty($msgArr)) {
                $msg = implode($msgArr, '<hr />');
            }
            $item .= '<td class="typo3-debugbar-table-cell-content">' . $this->fw($msg) . '</td>';
            $out .= '<tr>' . $item . '</tr>';
            $c++;
        }
        $output = '<table class="typo3-debugbar-table">' . $out . '</table>';

        return $output;
    }
}
