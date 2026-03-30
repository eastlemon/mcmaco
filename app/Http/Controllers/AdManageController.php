<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdManageController extends Controller
{
    /**
     * Мои объявления.
     */
    public function index(Request $request): View
    {
        $ads = Ad::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('ads.manage.index', compact('ads'));
    }

    /**
     * Форма создания.
     */
    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('ads.manage.form', [
            'ad' => new Ad(),
            'categories' => $categories,
            'mode' => 'create',
        ]);
    }

    /**
     * Сохранение нового объявления.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAd($request);
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';
        $data['views'] = 0;

        $ad = Ad::query()->create($data);

        return redirect()
            ->route('ads.manage.edit', $ad)
            ->with('status', 'Объявление создано. Теперь можно загрузить фото.');
    }

    /**
     * Форма редактирования.
     */
    public function edit(Request $request, Ad $ad): View
    {
        $this->authorizeOwner($request, $ad);

        $categories = Category::query()->orderBy('name')->get();
        $ad->load('images');

        return view('ads.manage.form', [
            'ad' => $ad,
            'categories' => $categories,
            'mode' => 'edit',
        ]);
    }

    /**
     * Обновление объявления.
     */
    public function update(Request $request, Ad $ad): RedirectResponse
    {
        $this->authorizeOwner($request, $ad);

        $data = $this->validateAd($request);
        $ad->update($data);

        return back()->with('status', 'Объявление обновлено.');
    }

    private function validateAd(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'city' => ['required', 'string', 'max:255'],
            'condition' => ['required', 'in:new,used'],
        ]);
    }

    private function authorizeOwner(Request $request, Ad $ad): void
    {
        if ($request->user()->id !== $ad->user_id) {
            abort(403);
        }
    }
}
