<?php
namespace App\Http\Controllers;
use App\Folder;
use App\Http\Requests\CreateTask;
use App\Http\Requests\EditTask;
use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    /**
     * タスク一覧
     * @param Folder $folder
     * @return \Illuminate\View\View
     */
    public function index(Folder $folder)
    {
        // ユーザーのフォルダを取得する
        $folders = Auth::user()->folders()->get();
        // 選ばれたフォルダに紐づくタスクを取得する
        $tasks = $folder->tasks()->get();
        return view('tasks/index', [
            'folders' => $folders,
            'current_folder_id' => $folder->id,
            'tasks' => $tasks,
        ]);
    }
    /**
     * タスク作成フォーム
     * @param Folder $folder
     * @return \Illuminate\View\View
     */
    public function showCreateForm(Folder $folder)
    {
        return view('tasks/create', [
            'folder_id' => $folder->id,
        ]);
    }
    /**
     * タスク作成
     * @param Folder $folder
     * @param CreateTask $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Folder $folder, CreateTask $request)
    {
        $task = new Task();
        $task->title = $request->title;
        $task->due_date = $request->due_date;
        $folder->tasks()->save($task);
        return redirect()->route('tasks.index', [
            'id' => $folder->id,
        ]);
    }
    /**
     * タスク編集フォーム
     * @param Folder $folder
     * @param Task $task
     * @return \Illuminate\View\View
     */
    public function showEditForm(Folder $folder, Task $task)
    {
        $this->checkRelation($folder, $task);
        return view('tasks/edit', [
            'task' => $task,
        ]);
    }
    /**
     * タスク編集
     * @param Folder $folder
     * @param Task $task
     * @param EditTask $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit(Folder $folder, Task $task, EditTask $request)
    {
        $this->checkRelation($folder, $task);
        $task->title = $request->title;
        $task->status = $request->status;
        $task->due_date = $request->due_date;
        $task->save();
        return redirect()->route('tasks.index', [
            'id' => $task->folder_id,
        ]);
    }
    /**
     * フォルダとタスクの関連性があるか調べる
     * @param Folder $folder
     * @param Task $task
     */
    private function checkRelation(Folder $folder, Task $task)
    {
        if ($folder->id !== $task->folder_id) {
            abort(404);
        }
    }
   
    public function export( Folder $folder, Request $request )
    {   
        $response = new StreamedResponse (function() use ($request){
 
            $stream = fopen('php://output', 'w');

            //　文字化け回避
            stream_filter_prepend($stream,'convert.iconv.utf-8/cp932//TRANSLIT');
 
            // タイトルを追加
            fputcsv($stream, ['id','folder_id','title','due_date','created_at','updated_at']);

            $tasks = Task::get();

            foreach ($tasks as $row) {
                fputcsv($stream, [$row->id,
                                  $row->folder_id,
                                  $row->title,
                                  $row->due_date,
                                  $row->created_at,
                                  $row->updated_at]);
            }
            fclose($stream);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="TaskList.csv"');
 
        return $response;
    }
}
