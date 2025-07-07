<!-- Assigned Trainings Section -->
<div class="row">
                        <div class="col-12">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Certificates</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="row">
                                        <?php if (empty($assignedTrainings)): ?>
                                            <div class="col-12">
                                                <div class="callout callout-success text-center">
                                                    <h5><i class="fas fa-exclamation-circle"></i> No Certificates To Show</h5>
                                                    <p>You do not have any certificates yet.</p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($assignedTrainings as $training): ?>
                                                <div class="col-md-4 mb-4">
                                                    <div class="card bg-light shadow-md">
                                                        <div class="card-header text-truncate">
                                                            <h5 class="card-title font-weight-bold"><?= htmlspecialchars($training['training_title']) ?></h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text text-truncate"><?= htmlspecialchars($training['description']) ?></p>
                                                            <p class="card-text"><small class="text-muted">
                                                                Duration: <?= date("F j, Y", strtotime($training['start_date'])) . ' to ' . date("F j, Y", strtotime($training['end_date'])) ?>
                                                            </small></p>
                                                            <a href="trainingDetails.php?trainingID=<?= $training['training_id'] ?>" class="btn btn-outline-success btn-md rounded-0">
                                                                View Details <i class="fas fa-arrow-circle-right ml-1"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <!-- /.row -->
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->