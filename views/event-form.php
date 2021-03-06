<form method="post" action="">
    <input type="hidden" name="action" value="saveevents">
    <div class="calendar_input">
        <div>
            <button name="send"><?=$this->text('label_save')?></button>
            <button name="add[0]" value="add" title="<?=$this->text('label_add_event')?>"><span class="fa fa-plus fa-fw"></span></button>
        </div>
<?php foreach ($this->events as $i => $event):?>
        <div>
            <label>
                <?=$this->text('event_date_start')?>
                <input type="date" class="calendar_input_date" maxlength="10" name="datestart[<?=$this->escape($i)?>]" value="<?=$this->escape($event->datestart)?>" id="datestart<?=$this->escape($i)?>">
            </label>
<?php   if ($this->showEventTime):?>
            <label>
                <?=$this->text('event_time')?>
                <input type="time" class="calendar_input_time" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
            </label>
<?php   else:?>
            <input type="hidden" maxlength="5" name="starttime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->starttime)?>">
<?php   endif?>
            <label>
                <?=$this->text('event_date_end')?>
                <input type="date" class="calendar_input_date" maxlength="10" name="dateend[<?=$this->escape($i)?>]" value="<?=$this->escape($event->dateend)?>" id="dateend<?=$this->escape($i)?>">
            </label>
<?php   if ($this->showEventTime):?>
            <label>
                <?=$this->text('event_time')?>
                <input type="time" class="calendar_input_time" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
            </label>
<?php   else:?>
            <input type="hidden" maxlength="5" name="endtime[<?=$this->escape($i)?>]" value="<?=$this->escape($event->endtime)?>">
<?php   endif?>
            <label>
                <?=$this->text('event_event')?>
                <input class="calendar_input_event event_highlighting" type="text" name="event[<?=$this->escape($i)?>]" value="<?=$this->escape($event->event)?>">
            </label>
<?php   if ($this->showEventLocation):?>
            <label>
                <?=$this->text('event_location')?>
                <input type="text" class="calendar_input_event" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
            </label>
<?php   else:?>
            <input type="hidden" name="location[<?=$this->escape($i)?>]" value="<?=$this->escape($event->location)?>">
<?php   endif?>
<?php   if ($this->showEventLink):?>
            <label>
                <?=$this->text('event_link')?>
                <input type="text" class="calendar_input_event" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
            </label>
            <label>
                <?=$this->text('event_link_txt')?>
                <input type="text" class="calendar_input_event" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
            </label>
<?php   else:?>
            <input type="hidden" name="linkadr[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linkadr)?>">
            <input type="hidden" name="linktxt[<?=$this->escape($i)?>]" value="<?=$this->escape($event->linktxt)?>">
<?php   endif?>
            <div>
                <button name="delete[<?=$this->escape($i)?>]" value="delete" title="<?=$this->text('label_delete_event')?>"><span class="fa fa-trash fa-fw"></span></button>
            </div>
        </div>
<?php endforeach?>
        <div>
            <button name="send"><?=$this->text('label_save')?></button>
            <button name="add[0]" value="add" title="<?=$this->text('label_add_event')?>"><span class="fa fa-plus fa-fw"></span></button>
        </div>
    </div>
</form>
