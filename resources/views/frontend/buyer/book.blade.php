@extends('frontend.layouts._app')
@section('title', 'Book ' . ($seller->name ?? 'Service'))
@section('content')
<div class="min-h-screen bg-slate-50 pt-20 pb-16 px-4">
    <div class="max-w-2xl mx-auto py-6">

        {{-- Back --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('frontend.service.show', $seller->slug ?? $seller->id) }}"
               class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Book Appointment</h1>
                <p class="text-xs text-slate-500">with {{ $seller->name }}</p>
            </div>
        </div>

        {{-- Seller Quick Info --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6 flex items-center gap-4">
            @if($seller->profile_photo)
                <img src="{{ asset($seller->profile_photo) }}"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($seller->name) }}&size=80&background=3b82f6&color=fff'"
                     class="w-14 h-14 rounded-2xl object-cover shrink-0">
            @else
                <div class="w-14 h-14 rounded-2xl bg-teal-700 text-white flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($seller->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <p class="font-bold text-slate-900">{{ $seller->name }}</p>
                <p class="text-xs text-slate-500">{{ $seller->title ?? $seller->designation ?? 'Professional' }}</p>
                @if($seller->city)
                    <p class="text-xs text-slate-400 mt-0.5"><i class="fa-solid fa-location-dot mr-1"></i>{{ $seller->city }}</p>
                @endif
            </div>
        </div>

        <form action="{{ route('buyer.book.store') ?? '#' }}" method="POST" id="bookingForm"
              data-track="booking_submit" data-track-lead="1">
            @csrf
            <input type="hidden" name="seller_id" value="{{ $seller->id }}">
            <input type="hidden" name="selected_date" id="selectedDate">
            <input type="hidden" name="selected_slot" id="selectedSlot">

            {{-- Step 1: Pick Date --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-700 text-white rounded-lg flex items-center justify-center text-xs font-black">1</span>
                    Pick a Date
                </h2>

                {{-- Month Navigation --}}
                <div class="flex items-center justify-between mb-4">
                    <button type="button" onclick="changeMonth(-1)" class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <p class="font-bold text-slate-900 text-sm" id="calMonthLabel"></p>
                    <button type="button" onclick="changeMonth(1)" class="w-8 h-8 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>

                {{-- Day Headers --}}
                <div class="grid grid-cols-7 gap-1 mb-2">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                    <div class="text-center text-[10px] font-bold text-slate-400">{{ $d }}</div>
                    @endforeach
                </div>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-1" id="calGrid"></div>

                <p class="text-xs text-slate-400 mt-3">
                    <span class="inline-block w-3 h-3 rounded bg-teal-700 mr-1 align-middle"></span>Available &nbsp;
                    <span class="inline-block w-3 h-3 rounded bg-slate-100 border border-slate-200 mr-1 align-middle"></span>Unavailable
                </p>
            </div>

            {{-- Step 2: Pick Time Slot --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4" id="slotSection">
                <h2 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-700 text-white rounded-lg flex items-center justify-center text-xs font-black">2</span>
                    Pick a Time Slot
                </h2>
                <p class="text-xs text-slate-400 mb-4" id="slotDateLabel">Select a date first</p>

                <div id="slotPeriods" class="space-y-4">
                    <p class="text-sm text-slate-400 text-center py-6">← Choose a date to see available slots</p>
                </div>
            </div>

            {{-- Step 3: Your Details --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 mb-4">
                <h2 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-teal-700 text-white rounded-lg flex items-center justify-center text-xs font-black">3</span>
                    Your Details
                </h2>
                <div class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required
                                placeholder="+1 555 000 0000"
                                class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Message / Notes</label>
                        <textarea name="message" rows="3" placeholder="Describe what you need help with..."
                            class="w-full px-4 py-3 border border-slate-200 rounded-2xl text-sm text-slate-800 font-medium placeholder-slate-400 focus:outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-50 transition resize-none">{{ old('message') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Booking Summary --}}
            <div id="bookingSummary" class="hidden bg-teal-50 border border-teal-100 rounded-2xl p-4 mb-4">
                <h3 class="font-bold text-teal-900 text-sm mb-2">Booking Summary</h3>
                <div class="space-y-1.5 text-sm text-teal-800">
                    <p><span class="font-semibold">Professional:</span> {{ $seller->name }}</p>
                    <p><span class="font-semibold">Date:</span> <span id="summaryDate">—</span></p>
                    <p><span class="font-semibold">Time:</span> <span id="summarySlot">—</span></p>
                </div>
            </div>

            <button type="submit" id="submitBtn" disabled
                class="w-full bg-slate-300 text-slate-500 font-bold py-4 rounded-2xl text-sm transition cursor-not-allowed"
                onclick="return validateBooking()">
                Confirm Booking
            </button>
        </form>
    </div>
</div>

<script>
const workingDays = @json($schedule['working_days'] ?? ['mon','tue','wed','thu','fri']);
const periods = @json($schedule['periods'] ?? [['label'=>'Morning','from'=>'09:00','to'=>'12:00','duration'=>60],['label'=>'Afternoon','from'=>'13:00','to'=>'17:00','duration'=>60]]);
const bookedSlots = @json($bookedSlots ?? []);
const dayNames = ['sun','mon','tue','wed','thu','fri','sat'];
const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

let curYear = new Date().getFullYear();
let curMonth = new Date().getMonth();
let selectedDate = null;
let selectedSlot = null;

function changeMonth(dir) {
    curMonth += dir;
    if (curMonth > 11) { curMonth = 0; curYear++; }
    if (curMonth < 0) { curMonth = 11; curYear--; }
    renderCalendar();
}

function renderCalendar() {
    document.getElementById('calMonthLabel').textContent = monthNames[curMonth] + ' ' + curYear;
    const grid = document.getElementById('calGrid');
    grid.innerHTML = '';
    const firstDay = new Date(curYear, curMonth, 1).getDay();
    const daysInMonth = new Date(curYear, curMonth + 1, 0).getDate();
    const today = new Date(); today.setHours(0,0,0,0);

    for (let i = 0; i < firstDay; i++) {
        grid.insertAdjacentHTML('beforeend', '<div></div>');
    }
    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(curYear, curMonth, d);
        const dayName = dayNames[date.getDay()];
        const isPast = date < today;
        const isWorking = workingDays.includes(dayName);
        const dateStr = curYear + '-' + String(curMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const isSelected = dateStr === selectedDate;

        let cls = 'text-center py-2 rounded-xl text-xs font-bold cursor-pointer transition ';
        if (isPast || !isWorking) {
            cls += 'text-slate-300 cursor-default';
        } else if (isSelected) {
            cls += 'bg-teal-700 text-white';
        } else {
            cls += 'text-slate-700 hover:bg-teal-50 hover:text-teal-700 border border-transparent hover:border-teal-200';
        }

        grid.insertAdjacentHTML('beforeend',
            `<div class="${cls}" ${(!isPast && isWorking) ? `onclick="selectDate('${dateStr}', '${date.toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric'})}', this)"` : ''}>${d}</div>`
        );
    }
}

function selectDate(dateStr, label, el) {
    selectedDate = dateStr;
    document.getElementById('selectedDate').value = dateStr;
    renderCalendar();
    renderSlots(dateStr, label);
}

function renderSlots(dateStr, label) {
    document.getElementById('slotDateLabel').textContent = label;
    const container = document.getElementById('slotPeriods');
    container.innerHTML = '';
    selectedSlot = null;
    document.getElementById('selectedSlot').value = '';
    updateSubmitBtn();

    periods.forEach(period => {
        const slots = generateSlots(period.from, period.to, period.duration || 60);
        if (!slots.length) return;

        let html = `<div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">${period.label || 'Available'}</p>
            <div class="flex flex-wrap gap-2">`;

        slots.forEach(slot => {
            const key = dateStr + '_' + slot.value;
            const isBooked = bookedSlots.includes(key);
            if (isBooked) {
                html += `<span class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-300 border border-slate-200 line-through cursor-not-allowed">${slot.label}</span>`;
            } else {
                html += `<button type="button" onclick="selectSlot('${slot.value}','${slot.label}',this)"
                    class="slot-btn px-4 py-2 rounded-xl text-xs font-bold bg-teal-50 text-teal-800 border border-teal-100 hover:bg-teal-700 hover:text-white hover:border-teal-700 transition">${slot.label}</button>`;
            }
        });

        html += '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
    });
}

function generateSlots(from, to, dur) {
    const slots = [];
    let [fh,fm] = from.split(':').map(Number);
    let [th,tm] = to.split(':').map(Number);
    let cur = fh*60+fm;
    const end = th*60+tm;
    while (cur + dur <= end) {
        const h = Math.floor(cur/60), m = cur%60;
        const ampm = h >= 12 ? 'PM' : 'AM';
        const h12 = h > 12 ? h-12 : (h === 0 ? 12 : h);
        const label = h12 + ':' + (m<10?'0'+m:m) + ' ' + ampm;
        const value = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
        slots.push({ label, value });
        cur += dur;
    }
    return slots;
}

function selectSlot(value, label, btn) {
    document.querySelectorAll('.slot-btn').forEach(b => {
        b.className = 'slot-btn px-4 py-2 rounded-xl text-xs font-bold bg-teal-50 text-teal-800 border border-teal-100 hover:bg-teal-700 hover:text-white hover:border-teal-700 transition';
    });
    btn.className = 'slot-btn px-4 py-2 rounded-xl text-xs font-bold bg-teal-700 text-white border border-teal-700';
    selectedSlot = value;
    document.getElementById('selectedSlot').value = value;
    document.getElementById('summaryDate').textContent = document.getElementById('slotDateLabel').textContent;
    document.getElementById('summarySlot').textContent = label;
    document.getElementById('bookingSummary').classList.remove('hidden');
    updateSubmitBtn();
}

function updateSubmitBtn() {
    const btn = document.getElementById('submitBtn');
    const ready = selectedDate && selectedSlot;
    btn.disabled = !ready;
    btn.className = `w-full font-bold py-4 rounded-2xl text-sm transition ${ready ? 'bg-teal-700 hover:bg-teal-800 text-white cursor-pointer' : 'bg-slate-300 text-slate-500 cursor-not-allowed'}`;
}

function validateBooking() {
    if (!selectedDate || !selectedSlot) {
        alert('Please select a date and time slot.');
        return false;
    }
    return true;
}

renderCalendar();
</script>
@endsection
