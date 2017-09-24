/**
 * Copyright 2017 Christoph M. Becker
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

if ("visibilityState" in document) {
    document.addEventListener("DOMContentLoaded", function () {
        var each = Array.prototype.forEach;

        function retrieveCalendar(calendar, url, isPop) {
            var request = new XMLHttpRequest();
            calendar.classList.add("calendar_loading");
            request.open("GET", url, true);
            request.setRequestHeader("X-Requested-With",  "XMLHttpRequest");
            request.onload = function () {
                if (this.status >= 200 && this.status < 300) {
                    replaceCalendar(calendar, this, isPop);
                }
                calendar.classList.remove("calendar_loading");
            };
            request.send();
        }

        function replaceCalendar(calendar, request, isPop) {
            calendar.innerHTML = request.response;
            initCalendar(calendar);
            if (!isPop) {
                var url = request.responseURL || request.getResponseHeader("X-Location");
                history.pushState({calendar_url: url}, document.title, url);
            }
        }

        function initCalendar(calendar) {
            each.call(calendar.getElementsByClassName("calendar_monthyear"), function (heading) {
                each.call(heading.getElementsByTagName("a"), function (anchor) {
                    anchor.onclick = function (event) {
                        retrieveCalendar(calendar, anchor.href);
                        event.preventDefault();
                    };
                });
            });
        }

        history.replaceState({calendar_url: location.href}, document.title, location.href);
        window.addEventListener("popstate", function (event) {
            if ("calendar_url" in event.state) {
                var container = document.getElementsByClassName("calendar_calendar")[0];
                retrieveCalendar(container, event.state.calendar_url, true);
            }
        });
        each.call(document.getElementsByClassName("calendar_calendar"), function (calendar) {
            initCalendar(calendar);
        });
    });
}