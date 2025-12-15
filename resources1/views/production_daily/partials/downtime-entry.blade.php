<div class="bg-white p-4 rounded-lg border border-yellow-300" data-index="{{ $index }}">
    <div class="flex items-center justify-between mb-3">
        <h4 class="font-semibold text-gray-700">Downtime #{{ $index + 1 }}</h4>
        <button type="button" class="removeDowntimeBtn text-red-600 hover:text-red-800 text-sm font-semibold">
            Hapus
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Downtime <span class="text-red-500">*</span></label>
            <select name="downtimes[{{ $index }}][downtime_type]" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Pilih Jenis --</option>
                @foreach(\App\Models\ProductionDailyDowntime::getDowntimeTypes() as $key => $value)
                    <option value="{{ $key }}" {{ (isset($downtime) && $downtime->downtime_type == $key) ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Include OEE</label>
            <div class="flex items-center mt-2">
                <input type="checkbox" name="downtimes[{{ $index }}][include_oee]" value="1" {{ (isset($downtime) && $downtime->include_oee) ? 'checked' : 'checked' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label class="ml-2 text-sm text-gray-700">Masukkan ke perhitungan OEE</label>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Mulai <span class="text-red-500">*</span></label>
            <input type="time" name="downtimes[{{ $index }}][start_time]" value="{{ isset($downtime) ? substr($downtime->start_time, 0, 5) : '' }}" required class="downtime-start-time w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Selesai <span class="text-red-500">*</span></label>
            <input type="time" name="downtimes[{{ $index }}][end_time]" value="{{ isset($downtime) ? substr($downtime->end_time, 0, 5) : '' }}" required class="downtime-end-time w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Durasi (Menit)</label>
            <input type="text" class="downtime-duration w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100" value="{{ isset($downtime) ? $downtime->duration_minutes . ' menit' : '' }}" readonly>
            <input type="hidden" name="downtimes[{{ $index }}][duration_minutes]" class="downtime-duration-minutes" value="{{ isset($downtime) ? $downtime->duration_minutes : '' }}">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
            <textarea name="downtimes[{{ $index }}][description]" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Keterangan downtime...">{{ isset($downtime) ? $downtime->description : '' }}</textarea>
        </div>
    </div>
</div>

