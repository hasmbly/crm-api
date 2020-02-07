<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class Comment
{ 

     /**
      * [createComment description]
      * this func for tindak lanjut from OP and Sanggahan from Masyarakat.
      * @return [type] [description]
      */
     public static function addComment( $request, $tblName ) {

      /*check if ticket is closed */
      // Code...

      try {

        $no_pengaduan     = $request->input('no_pengaduan');
        $comment          = $request->input('comment');
        $is_operator      = $request->input('is_operator');
        $created_by       = $request->input('created_by');

        /* Validator */

          $rules = array(

            'no_pengaduan' => 'required',
            'comment'      => 'required',

                  );    

          $messages = array(

            'no_pengaduan.required'       => 'Silahkan masukan No Pengaduan',
            'comment.required'            => 'Silahkan masukan Comment'
                      
          );

          $validator = Validator::make( $request->all(), $rules, $messages );

        if ($validator->fails()) {

            return response()->json([
                'error'    => true,
                'message'  => $validator->errors()->first()
            ], 422);

        }

        // Check if Pengaduan is closed
        
        $checkStatusClosed = DB::table($tblName)->where('no_pengaduan', $no_pengaduan)->where('status', 1)->pluck('status');

        if ( count($checkStatusClosed) != 0  ) {

            return response()->json([
            'error'         => true,
            'message'       => 'Maaf Pengaduan anda sudah Closed'
          ], 400);          

        }

        // Check if Comment from masyarakat
        if ($created_by == '' || $created_by == null) {
            $getName = DB::table('tbl_pengaduan')->where('no_pengaduan', $no_pengaduan)->pluck('nama');
            $created_by = $getName[0];
        }

        // check if is_operator 
        if ($is_operator == '') {
          $is_operator = 0;
        }

        if ( $tblName == 'tbl_pengaduan' ) {

        $InsertComment = DB::table('tbl_comment')->insertGetId(array(
                      
                      'no_pengaduan'     => $no_pengaduan,
                      'comment'          => $comment,
                      'is_operator'      => $is_operator,
                      'created_by'       => $created_by
                
         ));

        } elseif ( $tblName == 'tbl_pengaduan_iask' ) {

        $InsertComment = DB::table('tbl_comment_iask')->insertGetId(array(
                      
                      'no_pengaduan'     => $no_pengaduan,
                      'comment'          => $comment,
                      'is_operator'      => $is_operator,
                      'created_by'       => $created_by
                
         ));

        }



      } catch (QueryException $e) {

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);

      }
            return response()->json([
            'error'         => false,
            'message'       => 'Success Add Comment'
          ], 200);

     }

     /**
      * [showCommentByTicket description]
      * @param  [type] $ticket [description]
      * @return [type]         [description]
      */
     public static function show( $request, $tblName ) {

        $no_pengaduan = $request->input('no_pengaduan');
        $nik          = $request->input('nik');

        try {

        if ( $tblName == 'tbl_pengaduan' ) {

        $resultComment = DB::table('tbl_comment')
                        ->join($tblName.'', $tblName.'.no_pengaduan', '=', 'tbl_comment.no_pengaduan')
                        ->join('tbl_pertanyaan', 'tbl_pertanyaan.id_pertanyaan', '=', $tblName.'.id_pertanyaan')
                        ->select(
                          $tblName.'.no_pengaduan',
                          'tbl_pertanyaan.pertanyaan',
                          'tbl_comment.comment',
                          'tbl_comment.is_operator',
                          'tbl_comment.created_by',
                          'tbl_comment.created_at'
                        )
                        ->where('tbl_comment.no_pengaduan', $no_pengaduan)
                        ->where($tblName.'.nik', $nik)
                        ->get();

        } elseif ( $tblName == 'tbl_pengaduan_iask' ) {

        $resultComment = DB::table('tbl_comment_iask')
                        ->join($tblName.'', $tblName.'.no_pengaduan', '=', 'tbl_comment_iask.no_pengaduan')
                        ->select(
                          $tblName.'.no_pengaduan',
                          $tblName.'.uraian',
                          'tbl_comment_iask.comment',
                          'tbl_comment_iask.is_operator',
                          'tbl_comment_iask.created_by',
                          'tbl_comment_iask.created_at'
                        )
                        ->where('tbl_comment_iask.no_pengaduan', $no_pengaduan)
                        ->where($tblName.'.nik', $nik)
                        ->get();

        }


      
      } catch(QueryException $e) {

            return response()->json([
            'error'       => true,
            'code'        => $e->getCode(),
            'message'     => $e->getMessage()
          ], 500);
      }     

        
              return response()->json([
              'error'         => false,
              'message'      => $resultComment
            ], 200);
    }



  }