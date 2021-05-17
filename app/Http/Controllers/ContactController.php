<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Repositories\ContactRepository;
use App\Http\Resources\Contact as ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        ContactRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Get contact pre requisite
     * @get ("/api/contacts/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Get all contacts
     * @get ("/api/contacts")
     * @return array
     */
    public function index()
    {
        return $this->repo->paginate();
    }

    /**
     * Create contact
     * @post ("/api/contacts")
     * @param ({
     *      @Parameter("name", type="string", required="optional", description="Contact name"),
     *      @Parameter("email", type="email", required="true", description="Contact email"),
     * })
     * @return array
     */
    public function store(ContactRequest $request)
    {
        $contact = $this->repo->create();

        $contact = new ContactResource($contact);

        return $this->success(['message' => __('global.added', ['attribute' => __('contact.contact')]), 'contact' => $contact]);
    }

    /**
     * Get contact detail
     * @get ("/api/contacts/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Contact unique id"),
     * })
     * @return ContactResource
     */
    public function show($uuid)
    {
        $contact = $this->repo->findByUuidOrFail($uuid);

        return new ContactResource($contact);
    }

    /**
     * Update contact
     * @patch ("/api/contacts/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Contact unique id"),
     *      @Parameter("name", type="string", required="optional", description="Contact name"),
     *      @Parameter("email", type="string", required="true", description="Contact email"),
     * })
     * @return array
     */
    public function update(ContactRequest $request, Contact $contact)
    {
        $contact = $this->repo->update($contact);

        return $this->success(['message' => __('global.updated', ['attribute' => __('contact.contact')])]);
    }

    /**
     * Delete contact
     * @delete ("/api/contacts/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="uuid", required="true", description="Contact unique id"),
     * })
     * @return array
     */
    public function destroy($uuid)
    {
        $contact = $this->repo->findByUuidOrFail($uuid);

        $this->repo->delete($contact);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('contact.contact')])]);
    }
}