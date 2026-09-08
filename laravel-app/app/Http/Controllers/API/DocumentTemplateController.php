<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    /**
     * Display a listing of document templates (for both admin and users).
     */
    public function index(Request $request)
    {
        try {
            $query = DocumentTemplate::with(['creator:id,name,name_kh,email']);

            // Filter by category
            if ($request->filled('category') && $request->category !== 'ALL') {
                $query->where('category', $request->category);
            }

            // Search by title or description
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('file_name', 'like', "%{$search}%");
                });
            }

            $query->orderBy('created_at', 'desc');

            if ($request->boolean('no_paginate')) {
                $templates = $query->get();
                return response()->json([
                    'status' => 'success',
                    'data' => $templates,
                ]);
            }

            $perPage = $request->input('per_page', 10);
            $templates = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $templates,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch document templates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new document template (Admin only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $sizeInBytes = $file->getSize();
            $formattedSize = $this->formatFileSize($sizeInBytes);

            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('document-templates', $filename, 'public');

            $template = DocumentTemplate::create([
                'title' => $request->title,
                'category' => $request->category ?? 'GENERAL',
                'description' => $request->description,
                'file_path' => $path,
                'file_name' => $originalName,
                'file_size' => $formattedSize,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'បានរក្សាទុកគំរូឯកសារដោយជោគជ័យ',
                'data' => $template->load('creator:id,name,name_kh'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'បរាជ័យក្នុងការរក្សាទុកគំរូឯកសារ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing document template (Admin only).
     */
    public function update(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:51200',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'title' => $request->title,
                'category' => $request->category ?? $template->category,
                'description' => $request->description,
            ];

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
                    Storage::disk('public')->delete($template->file_path);
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $sizeInBytes = $file->getSize();
                $formattedSize = $this->formatFileSize($sizeInBytes);

                $filename = time() . '_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('document-templates', $filename, 'public');

                $data['file_path'] = $path;
                $data['file_name'] = $originalName;
                $data['file_size'] = $formattedSize;
            }

            $template->update($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'បានកែប្រែគំរូឯកសារដោយជោគជ័យ',
                'data' => $template->load('creator:id,name,name_kh'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'បរាជ័យក្នុងការកែប្រែគំរូឯកសារ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a document template (Admin only).
     */
    public function destroy($id)
    {
        $template = DocumentTemplate::findOrFail($id);

        try {
            DB::beginTransaction();

            if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }

            $template->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'បានលុបគំរូឯកសារដោយជោគជ័យ',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'បរាជ័យក្នុងការលុបគំរូឯកសារ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to format file size in human readable format.
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }
}
