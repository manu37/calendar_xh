<?php

/**
 * Copyright 2005-2006 Michael Svarrer
 * Copyright 2007-2008 Tory
 * Copyright 2008      Patrick Varlet
 * Copyright 2011      Holger Irmler
 * Copyright 2011-2013 Frank Ziesing
 * Copyright 2017      Christoph M. Becker
 *
 * This file is part of Calendar_XH.
 *
 * Calendar_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Calendar_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Calendar_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Calendar;

use stdClass;

class EventListController extends Controller
{
    private $month;

    private $year;

    private $endMonth;

    private $pastMonth;

    public function __construct($month, $year, $end_month, $past_month)
    {
        parent::__construct();
        $this->month = $month;
        $this->year = $year;
        $this->endMonth = $end_month;
        $this->pastMonth = $past_month;
    }

    public function defaultAction()
    {
        $this->determineYearAndMonth();
        $this->determineEndMonth();
    
        $display_end_month = $this->month + $this->endMonth + $this->pastMonth;
        $display_end_year = $this->year;
        while ($display_end_month > 12) {
            $display_end_year = $display_end_year + 1;
            $display_end_month = $display_end_month - 12;
        }

        $this->endMonth = $this->endMonth + $this->pastMonth + 1;

        list($events, $event_yearmonth_array) = $this->fetchEvents();

        $x = 1;

        $textmonth = date('F', mktime(1, 1, 1, $this->month, 1, $this->year));
        $monthnames = explode(',', $this->lang['monthnames_array']);

        $t = '';
        if ($this->conf['show_period_of_events']) {
            $t .= "<p class=\"period_of_events\">"
               .  $this->lang['text_announcing_overall_period']
               .  " <span>"
               .  $monthnames[$this->month - 1] . " "
               .  $this->year . "</span> "
               .  $this->lang['event_date_till_date']
               .  " <span>" . $monthnames[$display_end_month - 1]
               .  " " . $display_end_year . "</span></p>\n";
        }

        $t .= "<table border=\"0\" width=\"100%\">\n";

        $tablecols = $this->calcTablecols();

        while ($x <= $this->endMonth) {
            $textmonth = $monthnames[$this->month - 1];
            $today = (isset($today)) ? $today : date('j');
            $today = ($this->month == date('m') && $this->year == date('Y')) ? $today : 32;

            $table = false;
            /*headline with month, year and subheadline is being generated*/
            if (in_array("{$this->month}.{$this->year}", $event_yearmonth_array)) {
                $table = true;
            }
            if ($table) {
                $t .= new HtmlString($this->createHeadlineView($tablecols, $textmonth));
            }

            usort($events, function ($a, $b) {
                return strcmp($a->datetime, $b->datetime);
            });
            $t .= $this->renderEvents($events, $table, $tablecols, $textmonth);
            $x++;
            $this->advanceMonth();
        }
        $t .="</table>\n";
        echo $t;
    }

    private function determineYearAndMonth()
    {
        $month_input = isset($_GET['month']) ? $_GET['month'] : '';

        if ($this->month) {
            if ($month_input) {
                if ($this->month >= $month_input) {
                    $this->month = $month_input;
                }
            }
        } else {
            $this->month = $month_input;
        }

        $this->year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        if ($this->month == '') {
            $this->month = date('m');
        }

        if (!$this->pastMonth) {
            $this->pastMonth = $this->conf['show_number_of_previous_months'];
        }

        $this->month = $this->month - $this->pastMonth;
        if ($this->month < 1) {
            $this->year = $this->year - 1;
            $this->month = 12 + $this->month;
        }
    }

    private function determineEndMonth()
    {
        if ($this->endMonth == '') {
            if ($this->conf['show_number_of_future_months']) {
                $this->endMonth = $this->conf['show_number_of_future_months'];
            } else {
                $this->endMonth= "1";
            }
        }
    }

    private function advanceMonth()
    {
        if ($this->month == 12) {
            $this->year++;
            $this->month = 1;
        } else {
            $this->month++;
        }
    }

    private function fetchEvents()
    {
        $event_yearmonth_array  = array();
        $events = (new EventDataService)->readEvents();
        foreach ($events as $event) {
            list($event->startday, $event->startmonth, $event->startyear)
                = explode($this->dpSeperator(), $event->datestart);
            if (isset($event->dateend)) {
                list($event->endday, $event->endmonth, $event->endyear)
                    = explode($this->dpSeperator(), $event->dateend);
            } else {
                $event->endday = $event->endmonth = $event->endyear = null;
            }
            $event->datetime = "{$event->datestart} {$event->starttime}";
            $event->link = "{$event->linkadr},{$event->linktxt}";
            $event_yearmonth_array[] = "{$event->startmonth}.{$event->startyear}";
        }
        return [$events, $event_yearmonth_array];
    }

    private function calcTablecols()
    {
        // the number of tablecolumns is calculated
        // starting with minimum number of columns (date + main entry)
        $tablecols = 2;
        // adding columns according to config settings
        if ($this->conf['show_event_time']) {
            $tablecols++;
        }
        if ($this->conf['show_event_location']) {
            $tablecols++;
        }
        if ($this->conf['show_event_link']) {
            $tablecols++;
        }
        return $tablecols;
    }

    private function renderEvents(array $events, $table, $tablecols, $textmonth)
    {
        $t = '';
        foreach ($events as $event) {
            if ($event->startmonth != $this->month) {
                continue;
            }
            //=============================================
            //here the case of birthday annoncements starts
            //=============================================
            if (trim($event->location) == '###') {
                $age = $this->year - $event->startyear;
                if ($age >= 0) {
                    $this->month = sprintf('%02d', $this->month);

                    //headline with month has to be generated in case there is no ordinary event
                    if (!$table) {
                        $table = true;
                        $t .= $this->createHeadlineView($tablecols, $textmonth);
                    }
                    $t .= $this->createBirthdayRowView($event, $age);
                }
            }

            //==================
            // now normal events
            //==================
            if ($event->startyear == $this->year) {
                if ($this->month < 10) {
                    if (strlen($this->month) == 1) {
                        $this->month = '0' . $this->month;
                    }
                }
                $t .= $this->createEventRowView($event);
            }
        }
        return $t;
    }

    /**
     * @return View
     */
    private function createBirthdayRowView(stdClass $event, $age)
    {
        $view = new View('birthday-row');
        $view->event = $event;
        $view->age = $age;
        $view->date = $event->startday . $this->dpSeperator()
            . $this->month . $this->dpSeperator() . $this->year;
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        $view->link = new HtmlString($this->renderLink($event));
        return $view;
    }

    /**
     * @return View
     */
    private function createEventRowView(stdClass $event)
    {
        $view = new View('event-row');
        $view->event = $event;
        $view->date = new HtmlString($this->renderDate($event));
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        $view->link = new HtmlString($this->renderLink($event));
        $time = $event->starttime;
        if ($event->endtime) {
            if (!$event->endday) {
                $time .= ' ' . $this->lang['event_time_till_time'];
            }
            $time .= tag('br') . $event->endtime;
        }
        $view->time = new HtmlString($time);
        return $view;
    }

    private function renderDate($event)
    {
        $t = $event->startday;
        // if beginning and end dates are there, these are put one under the other
        if ($event->endday) {
            if ($this->month != $event->endmonth
                || $this->year != $event->endyear
            ) {
                $t .= $this->dpSeperator() . $this->month;
            }
            if ($this->year != $event->endyear) {
                $t .= $this->dpSeperator() . $this->year;
            }
            if ($this->year == $event->endyear && $this->dpSeperator() == '.') {
                $t.= '.';
            }
            $t .= "&nbsp;" . $this->lang['event_date_till_date'] . tag('br');
            $t .= $event->endday . $this->dpSeperator() . $event->endmonth
                . $this->dpSeperator() . $event->endyear;
        } else {
            $t .= $this->dpSeperator() . "{$this->month}" . $this->dpSeperator() . $this->year;
        }
        return $t;
    }

    private function renderLink(stdClass $event)
    {
        if (strpos($event->link, 'ext:') === 0) {
            $external_site = substr($event->link, 4);
            list($external_site, $external_text) = explode(',', $external_site);
            if (!$external_text) {
                $external_text = $external_site;
            }
            return "<td class=\"event_data event_link\"><a href=\"http://"
                . "{$external_site}\" target=\"_blank\" title=\""
                . strip_tags($event->event) . "\">$external_text</a></td>\n";
        } elseif (strpos($event->link, 'int:') === 0) {
            $internal_page = substr($event->link, 4);
            list($internal_page, $internal_text) = explode(',', $internal_page);
            if (!$internal_text) {
                $internal_text = $internal_page;
            }
            return "<td class=\"event_data event_link\"><a href=\"?"
                . "{$internal_page}\" title=\""
                . strip_tags($event->event) . "\">$internal_text</a></td>\n";
        } else {
            $t = "<td class=\"event_data event_link\">";
            if (substr($event->link, 0, 1) == ',') {
                $t .= substr(strip_tags($event->link), 1) . "</td>\n";
            } else {
                $t .= strip_tags($event->link) . "</td>\n";
            }
            return $t;
        }
    }

    private function createHeadlineView($tablecols, $textmonth)
    {
        $view = new View("event-list-headline");
        $view->tablecols = $tablecols;
        $view->textmonth = $textmonth;
        $view->year = $this->year;
        $view->showTime = $this->conf['show_event_time'];
        $view->showLocation = $this->conf['show_event_location'];
        $view->showLink = $this->conf['show_event_link'];
        return $view;
    }
}
