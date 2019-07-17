<?php

namespace ClassicO\NovaMediaLibrary\Core;

use Illuminate\Support\Facades\Validator;

class Controller {

	private $model = null;

	public function __construct() {
		$this->model = new Model;
	}

	/**
	 * Get all media files.
	 * You can filter by `description`, `type` or `created` date
	 *
	 * @return array
	 */
	public function get()
	{

		$valid_from = Validator::make(request()->only('from'), ['from' => 'nullable|date_format:Y-m-d']);
		$valid_to = Validator::make(request()->only('to'), ['to' => 'nullable|date_format:Y-m-d']);

		$from = $valid_from->fails() ? null : request('from');
		$to   = $valid_to->fails()   ? null : request('to');

		return $this->model->search(
			trim(htmlspecialchars(request('description', ''))),
			request('type'),
			request('step'),
			$from,
			$to
		);
	}

	/** Upload image to storage */
	function upload()
	{
		$file = request()->file('file');
		if ( !$file ) abort(422, __('nova-media-library::messages.not_uploaded'));

		$upload = new Upload($file);

		$upload->setType();
		if ( !$upload->type )
			abort(422, __('nova-media-library::messages.forbidden_file_format'));

		$upload->setName($file->getClientOriginalName());

		$upload->setFile();

		if ( !$upload->checkSize() )
			abort(422, __('nova-media-library::messages.size_limit_exceeded'));

		if ( $upload->save() ) {
			if ( $upload->noResize ) {
				abort(200, __('nova-media-library::messages.unsupported_resize', [ 'file' => $file->getClientOriginalName() ]));
			}
			return;
		}

		abort(422, __('nova-media-library::messages.not_uploaded'));
	}

	/** Delete all selected files */
	function delete()
	{
		$valid = Validator::make(request()->only('ids'), ['ids' => 'required|array']);
		if ( $valid->fails() ) abort(422, __('nova-media-library::messages.variable_ids_array'));

		$get = Model::find(request('ids'));
		$delete = $this->model->deleteByIds(request('ids'));

		if ( count($get) > 0 ) {
			$array = [];
			foreach ($get as $key) {
				$array[] = Helper::getFolder($key->path);
			}

			Helper::storage()->delete($array);
		}

		return [ 'status' => !!$delete ];
	}

	/** Update description of media file */
	function update()
	{

		$valid = Validator::make(request()->all(), [
			'id' => 'required|numeric',
			'description' => 'required|string|max:250',
			'video_thumb_seconds' => 'numeric'
		]);
		if ( $valid->fails() ) abort(422, __('nova-media-library::messages.id_desc_incorrect'));

		$file = $this->model->updateData(request('id'), request()->all());



		return [ 'message' => __('nova-media-library::messages.successfully_updated') ];
	}


	/** Crop image from frontend */
	function crop()
	{
		$crop = new Crop(request()->toArray());
		if ( !$crop->form )
			abort(422, __('nova-media-library::messages.crop_disabled'));

		if ( !$crop->check() )
			abort(422, __('nova-media-library::messages.invalid_request'));

		$crop->make();

		$crop->setSize();

		if ( $crop->save() ) return;

		abort(422, __('nova-media-library::messages.not_uploaded'));
	}
}
