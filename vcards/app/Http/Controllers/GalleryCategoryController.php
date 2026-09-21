<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGalleryCategoryRequest;
use App\Http\Requests\UpdateGalleryCategoryRequest;
use App\Models\GalleryCategory;

class GalleryCategoryController extends AppBaseController
{
    public function store(CreateGalleryCategoryRequest $request)
    {
        $input = $request->all();

        $category = GalleryCategory::create($input);

        return $this->sendResponse($category, __('messages.placeholder.gallery_category_created'));
    }

    public function edit(GalleryCategory $galleryCategory)
    {
        return $this->sendResponse($galleryCategory, 'Gallery category retrieved successfully.');
    }

    public function update(UpdateGalleryCategoryRequest $request, GalleryCategory $galleryCategory)
    {
        $input = $request->all();
        $galleryCategory->update($input);

        return $this->sendSuccess(__('messages.placeholder.gallery_category_updated'));
    }

    public function destroy(GalleryCategory $galleryCategory)
    {
        if ($galleryCategory->products()->exists()) {
            return $this->sendError(__('messages.placeholder.gallery_category_used'));
        }

        $galleryCategory->delete();

        return $this->sendSuccess(__('messages.placeholder.gallery_category_deleted'));
    }
}
