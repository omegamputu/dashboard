<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Section::make()->schema([
                    TextInput::make('title')
                        ->required()
                        ->live(debounce: '1000')
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                        ->maxLength(255),
                    TextInput::make('slug')->required()->maxLength(255),
                    Select::make('category_id')->relationship('category', 'name')
                        ->searchable()
                        ->preload()->createOptionForm([
                            TextInput::make('name')->live(debounce: '1000')
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                ->required()->maxLength(255),
                            TextInput::make('slug')->required()->maxLength(255),
                        ])
                        ->required(),
                    Select::make('author_id')->relationship('author', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    MarkdownEditor::make('content')
                        ->required()
                        ->columnSpan('full'),
                    TagsInput::make('tags')
                        ->separator(','),
                ])->columnSpan(2)->columns(2),

                Group::make()->schema([
                    Section::make("Image")->schema([
                        FileUpload::make('attachments')
                            ->image()
                            ->storeFileNamesIn('attachment_file_names')
                            ->disk('public')
                            ->directory('post')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->minSize(10)
                            ->maxSize(512)
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->maxFiles(2),
                    ])->collapsible(),

                    Section::make("Publishing")
                    ->description('Settings for publishing this post.')
                    ->schema([
                        Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'reviewing' => 'Reviewing',
                            'published' => 'Published',
                        ])->live(),
                        DatePicker::make('published_at')
                            ->hidden(fn (Get $get) => $get('status') !== 'published')
                            ->displayFormat('d/m/Y')
                            ->hoursStep(2)
                            ->minutesStep(15)
                            ->secondsStep(10)
                            ->maxDate(now()),
                        Checkbox::make('bring_to_light')
                        ->live()
                        ->hidden(fn (Get $get) => $get('status') !== 'published'),
                    ])->collapsible(),
                ]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('attachments'),
                TextColumn::make('title'),
                TextColumn::make('slug'),
                TextColumn::make('category.name'),
                TextColumn::make('tags'),
                TextColumn::make('status'),
                TextColumn::make('published_at'),
                CheckboxColumn::make('bring_to_light'),
            ])
            ->filters([
                //
                SelectFilter::make('author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'reviewing' => 'Reviewing',
                        'published' => 'Published',
                    ]),
                ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
