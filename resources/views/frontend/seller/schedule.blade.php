@extends('frontend.layouts.__prof_app')
@section('title', 'Booking Schedule')
@section('content')
<div class="pb-10">
    <div class="max-w-2xl mx-auto py-6 px-4 lg:px-0">
        <div class="mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Booking Schedule</h1>
            <p class="text-sm text-slate-500 mt-0.5">Set when you're available so clients can book time slots</p>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('seller.schedule.update') }}" method="POST" id="scheduleForm">
            @csrf

            {{-- Working Days --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-week text-teal-700 text-sm"></i> Working Days
                </h2>
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 sm:gap-2">
                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $i => $day)
                    <label class="day-toggle cursor-pointer">
                        <input type="checkbox" name="working_days[]" value="{{ strtolower($day) }}"
                            {{ in_array(strtolower($day), $schedule['working_days'] ?? ['mon','tue','wed','thu','fri']) ? 'checked' : '' }}
                            class="sr-only peer" onchange="updatePreview()">
                        <div class="text-center py-3.5 rounded-2xl border-2 border-slate-200 text-sm font-bold text-slate-400
                                    peer-checked:bg-teal-700 peer-checked:border-teal-700 peer-checked:text-white
                                    hover:border-teal-300 hover:text-teal-600 transition select-none">
                            {{ $day }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Time Slots by Period --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-teal-700 text-sm"></i> Time Slots
                </h2>
                <p class="text-xs text-slate-500 mb-5">Define your available time periods. Each period generates bookable slots automatically.</p>

                <div class="space-y-4" id="periodsContainer">

                    @foreach($schedule['periods'] ?? [['label'=>'Morning','from'=>'09:00','to'=>'12:00'],['label'=>'Afternoon','from'=>'13:00','to'=>'17:00']] as $i => $period)
                    <div class="period-block bg-slate-50 rounded-2xl border border-slate-200 p-4" data-index="{{ $i }}">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="period-label-badge text-xs font-bold px-3 py-1 rounded-full
                                    {{ $period['label'] === 'Morning' ? 'bg-amber-100 text-amber-700' : ($period['label'] === 'Afternoon' ? 'bg-teal-100 text-teal-800' : 'bg-purple-100 text-purple-700') }}">
                                    {{ $period['label'] }}
                                </span>
                                <input type="text" name="periods[{{ $i }}][label]" value="{{ $period['label'] }}"
                                    placeholder="Period name"
                                    class="text-sm font-semibold text-slate-700 bg-transparent border-0 focus:outline-none focus:bg-white focus:border focus:border-slate-200 focus:rounded-xl px-2 py-1 w-32">
                            </div>
                            <button type="button" onclick="removePeriod(this)" class="text-slate-300 hover:text-red-400 transition text-sm">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 mb-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">From</label>
                                <input type="time" name="periods[{{ $i }}][from]" value="{{ $period['from'] }}" onchange="updatePreview()"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">To</label>
                                <input type="time" name="periods[{{ $i }}][to]" value="{{ $period['to'] }}" onchange="updatePreview()"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">Slot Duration</label>
                                <select name="periods[{{ $i }}][duration]" onchange="updatePreview()"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                                    @foreach([15=>'15 min',30=>'30 min',45=>'45 min',60=>'1 hour',90=>'1.5 hours',120=>'2 hours'] as $val=>$lbl)
                                    <option value="{{ $val }}" {{ ($period['duration'] ?? 60) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">Buffer</label>
                                <select name="periods[{{ $i }}][buffer]"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                                    @foreach([0=>'None',10=>'10 min',15=>'15 min',30=>'30 min'] as $val=>$lbl)
                                    <option value="{{ $val }}" {{ ($period['buffer'] ?? 0) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                <button type="button" onclick="addPeriod()" class="mt-4 w-full py-3 rounded-2xl border-2 border-dashed border-slate-200 text-sm font-bold text-slate-400 hover:border-teal-300 hover:text-teal-700 transition">
                    <i class="fa-solid fa-plus mr-1"></i> Add Time Period
                </button>
            </div>

            {{-- Slot Preview --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-eye text-teal-700 text-sm"></i> Slot Preview
                </h2>
                <p class="text-xs text-slate-500 mb-4">How your available slots look to buyers</p>
                <div id="slotPreview" class="flex flex-wrap gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">9:00 AM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">10:00 AM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">11:00 AM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold border border-slate-200 line-through">12:00 PM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">1:00 PM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">2:00 PM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">3:00 PM</span>
                    <span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">4:00 PM</span>
                </div>
                <p class="text-xs text-slate-400 mt-3">
                    <span class="inline-block w-3 h-3 rounded bg-teal-50 border border-teal-100 mr-1 align-middle"></span>Available &nbsp;
                    <span class="inline-block w-3 h-3 rounded bg-slate-100 border border-slate-200 mr-1 align-middle"></span>Buffer/Booked
                </p>
            </div>

            {{-- Advance Booking & Max per Day --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-teal-700 text-sm"></i> Booking Rules
                </h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Max bookings per day</label>
                        <select name="max_per_day" class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                            @foreach([1,2,3,4,5,6,8,10] as $n)
                            <option value="{{ $n }}" {{ ($schedule['max_per_day'] ?? 4) == $n ? 'selected' : '' }}>{{ $n }} bookings</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Advance booking window</label>
                        <select name="advance_days" class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                            @foreach([7=>'1 week',14=>'2 weeks',30=>'1 month',60=>'2 months',90=>'3 months'] as $val=>$lbl)
                            <option value="{{ $val }}" {{ ($schedule['advance_days'] ?? 30) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Minimum notice</label>
                        <select name="min_notice_hours" class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                            @foreach([1=>'1 hour',2=>'2 hours',4=>'4 hours',12=>'12 hours',24=>'1 day',48=>'2 days'] as $val=>$lbl)
                            <option value="{{ $val }}" {{ ($schedule['min_notice_hours'] ?? 2) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Booking type</label>
                        <select name="booking_type" class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                            <option value="instant" {{ ($schedule['booking_type'] ?? '') === 'instant' ? 'selected' : '' }}>Instant confirmation</option>
                            <option value="manual" {{ ($schedule['booking_type'] ?? '') === 'manual' ? 'selected' : '' }}>Manual approval</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── WORKING HOURS ──────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-4 sm:p-6 mb-4">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h2 class="font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-business-time text-teal-700 text-sm"></i> Working Hours
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Show when you're available on your public profile</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer flex-shrink-0 mt-0.5">
                        <span class="text-xs font-semibold text-slate-500">Show on profile</span>
                        <input type="hidden" name="show_office_hours" value="0">
                        <div class="relative">
                            <input type="checkbox" name="show_office_hours" value="1"
                                id="showOfficeHoursToggle" class="sr-only peer"
                                {{ ($schedule['show_office_hours'] ?? false) ? 'checked' : '' }}
                                onchange="document.getElementById('officeHoursBody').classList.toggle('hidden',!this.checked)">
                            <div class="w-11 h-6 bg-slate-200 peer-checked:bg-teal-600 rounded-full transition-colors duration-200"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                        </div>
                    </label>
                </div>

                <div id="officeHoursBody" class="{{ ($schedule['show_office_hours'] ?? false) ? '' : 'hidden' }} space-y-5">

                    {{-- Meta row --}}
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Timezone</label>
                            @php
                            $tzList = [
                                'America/New_York'    => 'Eastern Time (ET)',
                                'America/Chicago'     => 'Central Time (CT)',
                                'America/Denver'      => 'Mountain Time (MT)',
                                'America/Phoenix'     => 'Mountain Time – Arizona',
                                'America/Los_Angeles' => 'Pacific Time (PT)',
                                'America/Anchorage'   => 'Alaska Time',
                                'Pacific/Honolulu'    => 'Hawaii Time',
                                'America/Puerto_Rico' => 'Atlantic Time',
                                'Europe/London'       => 'London (GMT/BST)',
                                'Europe/Paris'        => 'Central European Time',
                                'Asia/Dubai'          => 'Dubai (GST)',
                                'Asia/Karachi'        => 'Pakistan (PKT)',
                                'Asia/Dhaka'          => 'Bangladesh (BST)',
                                'Asia/Kolkata'        => 'India (IST)',
                                'Australia/Sydney'    => 'Sydney (AEST)',
                            ];
                            $savedTz = $schedule['office_hours']['timezone'] ?? 'America/New_York';
                            @endphp
                            <select name="office_hours[timezone]"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                                @foreach($tzList as $val => $label)
                                <option value="{{ $val }}" {{ $savedTz === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Response Time</label>
                            @php $savedRt = $schedule['office_hours']['response_time'] ?? ''; @endphp
                            <select name="office_hours[response_time]"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                                <option value="">— Select —</option>
                                @foreach(['30_min'=>'Within 30 minutes','1_hour'=>'Within 1 hour','4_hours'=>'Within 4 hours','24_hours'=>'Within 24 hours','48_hours'=>'Within 2 days'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ $savedRt === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Emergency + Note --}}
                    <div class="grid sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 bg-red-50 border border-red-100 rounded-2xl cursor-pointer hover:bg-red-100 transition">
                            <input type="hidden" name="office_hours[emergency_available]" value="0">
                            <input type="checkbox" name="office_hours[emergency_available]" value="1" id="emergencyAvailable"
                                {{ ($schedule['office_hours']['emergency_available'] ?? false) ? 'checked' : '' }}
                                class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                            <div>
                                <p class="text-sm font-bold text-red-700">Emergency Available</p>
                                <p class="text-xs text-red-500">24/7 emergency calls accepted</p>
                            </div>
                        </label>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Special Note <span class="font-normal text-slate-400">(optional)</span></label>
                            <input type="text" name="office_hours[note]"
                                value="{{ $schedule['office_hours']['note'] ?? '' }}"
                                placeholder="e.g. Closed public holidays"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                        </div>
                    </div>

                    {{-- Day-by-day schedule --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Weekly Schedule</label>
                            <button type="button" onclick="copyWeekdays()"
                                class="text-xs font-bold text-teal-600 hover:text-teal-800 transition">
                                <i class="fa-solid fa-copy mr-1"></i> Copy Mon → Weekdays
                            </button>
                        </div>
                        <div class="space-y-2">
                            @php
                            $ohDays = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
                            $defaultOpen = ['mon'=>true,'tue'=>true,'wed'=>true,'thu'=>true,'fri'=>true,'sat'=>false,'sun'=>false];
                            @endphp
                            @foreach($ohDays as $dayKey => $dayShort)
                            @php
                                $dayData = $schedule['office_hours']['days'][$dayKey] ?? null;
                                $isDayOpen = (bool)($dayData['open'] ?? $defaultOpen[$dayKey]);
                                $slots = $dayData['slots'] ?? [['from'=>'09:00','to'=>'17:00']];
                            @endphp
                            <div class="oh-day-row flex items-center gap-2 sm:gap-3 bg-slate-50 rounded-2xl border border-slate-200 px-3 py-2.5" data-day="{{ $dayKey }}">
                                {{-- Day label --}}
                                <div class="w-8 text-xs font-black text-slate-500 shrink-0">{{ $dayShort }}</div>
                                {{-- Toggle --}}
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="hidden" name="office_hours[days][{{ $dayKey }}][open]" value="0">
                                    <input type="checkbox" name="office_hours[days][{{ $dayKey }}][open]" value="1"
                                        class="sr-only peer oh-day-toggle"
                                        {{ $isDayOpen ? 'checked' : '' }}
                                        onchange="ohToggleDay(this,'{{ $dayKey }}')">
                                    <div class="w-9 h-5 bg-slate-200 peer-checked:bg-teal-600 rounded-full transition-colors duration-200"></div>
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-4"></div>
                                </label>
                                {{-- Status label --}}
                                <span class="oh-day-label text-xs font-bold w-10 shrink-0 {{ $isDayOpen ? 'text-teal-700' : 'text-slate-400' }}">
                                    {{ $isDayOpen ? 'Open' : 'Closed' }}
                                </span>
                                {{-- Slots --}}
                                <div class="oh-slots flex flex-wrap items-center gap-1.5 flex-1 {{ $isDayOpen ? '' : 'hidden' }}" id="ohSlots-{{ $dayKey }}">
                                    @foreach($slots as $si => $slot)
                                    <div class="oh-slot-pair flex items-center gap-1">
                                        <input type="time" name="office_hours[days][{{ $dayKey }}][slots][{{ $si }}][from]"
                                            value="{{ $slot['from'] ?? '09:00' }}"
                                            class="px-2 py-1 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-teal-600 bg-white w-[7rem]">
                                        <span class="text-slate-400 text-xs">–</span>
                                        <input type="time" name="office_hours[days][{{ $dayKey }}][slots][{{ $si }}][to]"
                                            value="{{ $slot['to'] ?? '17:00' }}"
                                            class="px-2 py-1 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-teal-600 bg-white w-[7rem]">
                                        @if($si > 0)
                                        <button type="button" onclick="ohRemoveSlot(this,'{{ $dayKey }}')"
                                            class="text-slate-300 hover:text-red-400 text-xs px-1">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach
                                    @if(count($slots) < 2)
                                    <button type="button" onclick="ohAddSplit('{{ $dayKey }}')"
                                        class="oh-add-split text-xs font-bold text-slate-400 hover:text-teal-600 transition px-1 whitespace-nowrap">
                                        + split
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            <button type="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-4 rounded-2xl text-base transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Save Schedule
            </button>
        </form>

    </div>
</div>

<script>
let periodCount = {{ count($schedule['periods'] ?? [['a','b']]) }};

function addPeriod() {
    const i = periodCount++;
    const labels = ['Morning','Afternoon','Evening','Custom'];
    const label = labels[i % labels.length];
    const colors = {'Morning':'bg-amber-100 text-amber-700','Afternoon':'bg-teal-100 text-teal-800','Evening':'bg-purple-100 text-purple-700','Custom':'bg-slate-100 text-slate-700'};
    const defaults = {'Morning':['09:00','12:00'],'Afternoon':['13:00','17:00'],'Evening':['18:00','21:00'],'Custom':['08:00','18:00']};
    const [from,to] = defaults[label] || ['09:00','17:00'];
    const html = `
    <div class="period-block bg-slate-50 rounded-2xl border border-slate-200 p-4" data-index="${i}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="period-label-badge text-xs font-bold px-3 py-1 rounded-full ${colors[label]}">${label}</span>
                <input type="text" name="periods[${i}][label]" value="${label}" placeholder="Period name"
                    class="text-sm font-semibold text-slate-700 bg-transparent border-0 focus:outline-none focus:bg-white focus:border focus:border-slate-200 focus:rounded-xl px-2 py-1 w-32">
            </div>
            <button type="button" onclick="removePeriod(this)" class="text-slate-300 hover:text-red-400 transition text-sm">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="text-xs font-semibold text-slate-500 mb-1 block">From</label>
                <input type="time" name="periods[${i}][from]" value="${from}" onchange="updatePreview()"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500 mb-1 block">To</label>
                <input type="time" name="periods[${i}][to]" value="${to}" onchange="updatePreview()"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex-1">
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Slot Duration</label>
                <select name="periods[${i}][duration]" onchange="updatePreview()"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                    <option value="15">15 min</option>
                    <option value="30">30 min</option>
                    <option value="45">45 min</option>
                    <option value="60" selected>1 hour</option>
                    <option value="90">1.5 hours</option>
                    <option value="120">2 hours</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="text-xs font-semibold text-slate-500 mb-1 block">Buffer</label>
                <select name="periods[${i}][buffer]"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-teal-600 bg-white">
                    <option value="0">None</option>
                    <option value="10">10 min</option>
                    <option value="15">15 min</option>
                    <option value="30">30 min</option>
                </select>
            </div>
        </div>
    </div>`;
    document.getElementById('periodsContainer').insertAdjacentHTML('beforeend', html);
}

function removePeriod(btn) {
    const block = btn.closest('.period-block');
    if (document.querySelectorAll('.period-block').length <= 1) {
        alert('Need at least one time period.');
        return;
    }
    if (!confirm('Remove this time period?')) return;
    block.remove();
    updatePreview();
}

// ── Working Hours JS ──────────────────────────────────────────────────
function ohToggleDay(checkbox, day) {
    const row   = checkbox.closest('.oh-day-row');
    const slots = document.getElementById('ohSlots-' + day);
    const label = row.querySelector('.oh-day-label');
    if (checkbox.checked) {
        slots.classList.remove('hidden');
        label.textContent = 'Open';
        label.className = 'oh-day-label text-xs font-bold w-10 shrink-0 text-teal-700';
    } else {
        slots.classList.add('hidden');
        label.textContent = 'Closed';
        label.className = 'oh-day-label text-xs font-bold w-10 shrink-0 text-slate-400';
    }
}

function ohAddSplit(day) {
    const slots  = document.getElementById('ohSlots-' + day);
    const addBtn = slots.querySelector('.oh-add-split');
    const count  = slots.querySelectorAll('.oh-slot-pair').length;
    const html = `
        <div class="oh-slot-pair flex items-center gap-1">
            <input type="time" name="office_hours[days][${day}][slots][${count}][from]"
                value="13:00"
                class="px-2 py-1 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-teal-600 bg-white w-[7rem]">
            <span class="text-slate-400 text-xs">–</span>
            <input type="time" name="office_hours[days][${day}][slots][${count}][to]"
                value="17:00"
                class="px-2 py-1 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-teal-600 bg-white w-[7rem]">
            <button type="button" onclick="ohRemoveSlot(this,'${day}')"
                class="text-slate-300 hover:text-red-400 text-xs px-1">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>`;
    if (addBtn) { addBtn.insertAdjacentHTML('beforebegin', html); addBtn.remove(); }
    else        { slots.insertAdjacentHTML('beforeend', html); }
}

function ohRemoveSlot(btn, day) {
    btn.closest('.oh-slot-pair').remove();
    const slots = document.getElementById('ohSlots-' + day);
    if (!slots.querySelector('.oh-add-split')) {
        slots.insertAdjacentHTML('beforeend',
            `<button type="button" onclick="ohAddSplit('${day}')"
                class="oh-add-split text-xs font-bold text-slate-400 hover:text-teal-600 transition px-1 whitespace-nowrap">
                + split</button>`);
    }
}

function copyWeekdays() {
    const monSlotsDiv = document.getElementById('ohSlots-mon');
    const monToggle   = document.querySelector('input[name="office_hours[days][mon][open]"][type="checkbox"]');
    const isOpen      = monToggle && monToggle.checked;
    const fromInputs  = monSlotsDiv ? [...monSlotsDiv.querySelectorAll('input[type="time"]')] : [];

    ['tue','wed','thu','fri'].forEach(day => {
        const toggle = document.querySelector(`input[name="office_hours[days][${day}][open]"][type="checkbox"]`);
        if (!toggle) return;
        toggle.checked = isOpen;
        ohToggleDay(toggle, day);

        const slotsDiv = document.getElementById('ohSlots-' + day);
        if (!slotsDiv) return;
        // Remove extra split slots, keep only first pair
        slotsDiv.querySelectorAll('.oh-slot-pair:not(:first-child)').forEach(p => p.remove());
        if (!slotsDiv.querySelector('.oh-add-split')) {
            slotsDiv.insertAdjacentHTML('beforeend',
                `<button type="button" onclick="ohAddSplit('${day}')"
                    class="oh-add-split text-xs font-bold text-slate-400 hover:text-teal-600 transition px-1 whitespace-nowrap">
                    + split</button>`);
        }
        // Copy time values
        const dayInputs = [...slotsDiv.querySelectorAll('input[type="time"]')];
        fromInputs.forEach((src, i) => { if (dayInputs[i]) dayInputs[i].value = src.value; });
    });
}
// ── End Working Hours JS ────────────────────────────────────────────────

function updatePreview() {
    const slots = [];
    document.querySelectorAll('.period-block').forEach(block => {
        const from = block.querySelector('[name*="[from]"]')?.value;
        const to = block.querySelector('[name*="[to]"]')?.value;
        const dur = parseInt(block.querySelector('[name*="[duration]"]')?.value || 60);
        if (!from || !to) return;
        let [fh,fm] = from.split(':').map(Number);
        let [th,tm] = to.split(':').map(Number);
        let cur = fh * 60 + fm;
        const end = th * 60 + tm;
        while (cur + dur <= end) {
            const h = Math.floor(cur/60), m = cur%60;
            const label = (h>12?h-12:h||12)+':'+(m<10?'0'+m:m)+' '+(h>=12?'PM':'AM');
            slots.push(label);
            cur += dur;
        }
    });
    const preview = document.getElementById('slotPreview');
    if (!slots.length) { preview.innerHTML = '<p class="text-xs text-slate-400">No slots generated yet.</p>'; return; }
    preview.innerHTML = slots.map(s =>
        `<span class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-800 text-xs font-bold border border-teal-100">${s}</span>`
    ).join('');
}
</script>
@endsection
