<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatRoomDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{   
    public function index(){
        return view('livewire.chatroom');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ctype' => 'required|string',
            'cdescription' => 'required|string',
            'creference' => 'nullable',
            'curl' => 'nullable'
        ]);

        DB::beginTransaction();

        try {
            // Active Complaint Check by same customer
            $existingComplaint = ChatRoom::where('creference', $request->creference)
                ->where('cstatus', 'in_progress')
                ->whereHas('applicant', function ($query) {
                    $query->where('ckdcust', Auth::user()->ckdcust);
                })
                ->exists();

            if ($existingComplaint) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already an active complaint for this item.'
                ], 422);
            }

            // Create Chat Room
            $chatRoom = ChatRoom::create([
                'nidcomplicant' => Auth::id(),
                'creference' => $request->creference,
                'ctype' => $request->ctype,
                'cissue' => $request->cissue,
                'curl' => $request->curl,
                'cdescription' => $request->cdescription,
                'cstatus' => 'in_progress',
            ]);

            // Invite all parties involved
            // Admins
            $admins = User::where('role', 'admin')->pluck('id')->toArray();

            // Default members (auth + admin)
            $memberIds = array_unique(array_merge(
                [Auth::id()],
                $admins
            ));

            // If the complaint is delivery, invite both warehouse PICs
            if ($request->ctype === 'delivery' && $request->creference) {
                $delivery = DB::table('ytsjhdr')
                    ->select('ckdwh_from', 'ckdwh_to')
                    ->where('cnosj', $request->creference)
                    ->first();

                if ($delivery) {
                    $warehouseFromUsers = User::where('ckdwh', $delivery->ckdwh_from)
                        ->pluck('id')
                        ->toArray();

                    $warehouseToUsers = User::where('ckdwh', $delivery->ckdwh_to)
                        ->pluck('id')
                        ->toArray();

                    $memberIds = array_unique(array_merge(
                        $memberIds,
                        $warehouseFromUsers,
                        $warehouseToUsers
                    ));
                }
            }

            // If the complaint is usage, invite customer's purchasing
            if ($request->ctype === 'usage') {
                $purchasings = User::where('ckdcust', Auth::user()->ckdcust)
                    ->where('customer_role', 'purchasing')
                    ->pluck('id')
                    ->toArray();
                    

                $memberIds = array_unique(array_merge(
                    $memberIds,
                    $purchasings
                ));
            }
            
            $insertData = [];

            foreach ($memberIds as $userId) {
                $insertData[] = [
                    'nidchatroom' => $chatRoom->nidchatroom,
                    'niduser' => $userId,
                    'nidlastreadmessage' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            ChatRoomDetail::insert($insertData);

            DB::commit();

            return response()->json([
                'success' => true,
                'room_id' => $chatRoom->nidchatroom,
                'message' => 'Complaint submitted successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }
}
