<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 全ユーザー許可（必要に応じて権限チェックを追加）
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after_or_equal:clock_in',
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end'   => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clock_in = $this->input('clock_in');
            $clock_out = $this->input('clock_out');

            // 出勤退勤の時間チェック
            if ($clock_in && $clock_out && strtotime($clock_in) > strtotime($clock_out)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です。');
            }

            $breaks = $this->input('breaks', []);
            $clock_in_time = $clock_in ? strtotime($clock_in) : null;
            $clock_out_time = $clock_out ? strtotime($clock_out) : null;

            foreach ($breaks as $index => $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $break_start = strtotime($break['break_start']);
                    $break_end = strtotime($break['break_end']);

                    // 勤務時間外チェック
                    if ($clock_in_time && $break_start < $clock_in_time || $clock_out_time && $break_end > $clock_out_time) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            '休憩時間が勤務時間外です。'
                        );
                    }

                    // 休憩時間の順序チェック
                    if ($break_start > $break_end) {
                        $validator->errors()->add(
                            "breaks.$index.break_start",
                            '休憩の開始時間が終了時間より遅くなっています。'
                        );
                    }
                }
            }

            // 休憩同士の重複チェック
            for ($i = 0; $i < count($breaks); $i++) {
                for ($j = $i + 1; $j < count($breaks); $j++) {
                    $start_i = !empty($breaks[$i]['break_start']) ? strtotime($breaks[$i]['break_start']) : null;
                    $end_i   = !empty($breaks[$i]['break_end'])   ? strtotime($breaks[$i]['break_end'])   : null;
                    $start_j = !empty($breaks[$j]['break_start']) ? strtotime($breaks[$j]['break_start']) : null;
                    $end_j   = !empty($breaks[$j]['break_end'])   ? strtotime($breaks[$j]['break_end'])   : null;

                    if ($start_i && $end_i && $start_j && $end_j) {
                        // オーバーラップ判定
                        if ($start_i < $end_j && $start_j < $end_i) {
                            $validator->errors()->add(
                                "breaks.$j.break_start",
                                "休憩時間が他の休憩と重複しています。"
                            );
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_out.required' => '退勤時間を入力してください。',
            'clock_out.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です。',
            'reason.required' => '備考を記入してください。',
            'reason.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
