<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkshopParticipantResource\Pages;
use App\Models\WorkshopParticipant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class WorkshopParticipantResource extends Resource
{
    protected static ?string $model = WorkshopParticipant::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Workshop Participants';
    protected static ?string $navigationGroup = 'Workshop Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\Select::make('workshop_id')
                    ->relationship('workshop', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('booking_transaction_id')
                    ->relationship('bookingTransaction', 'id')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('occupation')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Toggle::make('attendance_status')
                    ->label('Attendance')
                    ->onColor('success')
                    ->offColor('danger'),

                Forms\Components\TextInput::make('certificate_number')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpan('full'),
            ])->columns(2),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('workshop.name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('occupation')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('attendance_status')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('certificate_number')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('workshop_id')
                    ->relationship('workshop', 'name')
                    ->label('Workshop')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('attendance_status')
                    ->label('Attendance'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markAttended')
                        ->label('Mark as Attended')
                        ->action(function (collection $records) {
                            $records->each->update(['attendance_status' => true]);
                        })
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation(),
                        
                    Tables\Actions\BulkAction::make('generateCertificates')
                        ->label('Generate Certificates')
                        ->action(function (collection $records) {
                            $records->each(function ($record) {
                                if ($record->attendance_status) {
                                    $record->update([
                                        'certificate_number' => 'CERT-' . uniqid()
                                    ]);
                                }
                            });
                        })
                        ->icon('heroicon-o-document')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkshopParticipants::route('/'),
            'create' => Pages\CreateWorkshopParticipant::route('/create'),
            'edit' => Pages\EditWorkshopParticipant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['workshop', 'bookingTransaction']);
    }
}   