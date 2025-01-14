<?php

namespace App\Exports;

use App\Models\Nom\AcceleratorPhase;
use App\Models\Nom\AcceleratorProject;
use App\Models\Nom\Pillar;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PillarVotes implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public Pillar $pillar;

    public ?string $search;

    public ?string $sort;

    public ?string $order;

    public function __construct(Pillar $pillar, ?string $search = null, ?string $sort = null, ?string $order = null)
    {
        $this->pillar = $pillar;
        $this->search = $search;
        $this->sort = $sort;
        $this->order = $order;
    }

    public function headings(): array
    {
        return [
            'Vote',
            'Project',
            'Timestamp',
        ];
    }

    public function map($row): array
    {
        $vote = 'Abstain';

        if ($row->is_yes) {
            $vote = 'Yes';
        }

        if ($row->is_no) {
            $vote = 'No';
        }

        return [
            $vote,
            $row->project->name,
            $row->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function query()
    {
        $query = $this->pillar
            ->az_votes();

        if ($this->search) {
            $query->whereHasMorph('votable', [
                AcceleratorProject::class,
                AcceleratorPhase::class,
            ], function ($q) {
                $q->where('name', 'LIKE', "%{$this->search}%");
            });
        }

        if ($this->sort === 'project') {
            $query->orderBy(
                AcceleratorProject::select('name')->whereColumn('nom_accelerator_projects.id', 'nom_accelerator_project_votes.accelerator_project_id'),
                $this->order
            );
        } else {
            $query->orderBy($this->sort, $this->order);
        }

        return $query;
    }
}
