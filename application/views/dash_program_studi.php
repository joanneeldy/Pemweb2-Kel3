<style>
	.wrap {
		padding: 2em 0 1em 0;
		display: flex;
		gap: 2em;
		align-items: flex-end;
	}

	.fulls {
		flex: 1;
	}

	.btnarea {
		display: flex;
		justify-content: flex-end;
		align-items: center;
		gap: 2em;
		padding: 1em 0;
	}

	.btnarea-nopad {
		padding: 0;
	}

	.btnarea>button {
		transition: all 0.3s ease;
		background: #0079FF;
		cursor: pointer;
		color: white;
		border: none;
		padding: .75em 1.5em;
		border-radius: 0.5em;
	}

	.btnarea>a {
		color: #0079FF
	}

	.btnarea>button:hover {
		background: #015cc5;
	}

	.btnarea>button:active {
		transform: translateY(0.2em);
	}
</style>
<div id="appss"></div>
<script type="text/babel">
	const { useState, useEffect } = React
	const App = () => {
		const [showForm, setShowForm] = useState(null)
		const [listData, setListData] = useState([])
		const [showMessageSuccess, setShowMessageSuccess] = useState(false)
		const [editedData, setEditedData] = useState(null)
		// get data from database
		const getAllDataProgramStudi = () => {
			$.ajax({
				url: "<?=base_url()?>index.php/ProgramStudi/get_all_programstudi",
				method: 'GET',
				success: data => {
					setListData(JSON.parse(data))
				},
				error: () => {
					alert('Gagal mendapatkan data Program Studi')
				}
			})
		}
		// Fungsi untuk menangani klik tombol edit
		const handleEditData = (id, listData) => {
			setEditedData(listData.filter(it => it.id == id)[0])
			setShowForm('edit')
		}
		// Fungsi untuk menangani klik tombol delete
		const handleDeleteData = id => {
			if (confirm('Apakah Anda yakin ingin menghapus data program studi ini?')){
            // Kirim request AJAX untuk menghapus data program studi berdasarkan ID
				$.ajax({
					url: `<?= base_url() ?>index.php/ProgramStudi/delete_programstudi/${id}`,
					method: 'POST',
					success: function(data) {
						setShowMessageSuccess(true)
						setTimeout(() => setShowMessageSuccess(false),5000)
						// Refresh halaman setelah menghapus data
						getAllDataProgramStudi()
					},
					error: function() {
						alert('Gagal menghapus data program studi.');
					}
				});
			}
		}
		// menampilkan data ke dalam database ketika ada perubahan state
		useEffect(() => {
			$(`#listdata`).DataTable({
				destroy: true,
				data: listData,
				columns: [
					{ data: 'nama', title: 'Nama' },
					{ data: 'program_pendidikan', title: 'Program Pendidikan' },
					{ data: 'akreditasi', title: 'Akreditasi' },
					{ data: 'sk_akreditasi', title: 'SK Akreditasi' },
					{ 
						data: null,
    					render: function (data, type, row) {
        					return `
							<i title="Edit Program Studi" class="fa-solid fa-pen-to-square btn-edit" data-id="${data.id}"></i>
							<i title="Hapus Program Studi" class="fa-solid fa-trash" data-id="${data.id}"></i>
        					`;
    					},
    					title: 'Action' 
					},
				],
				initComplete: function(){
					$('#listdata').off().on('click', 'tr td i', function(){
						const buttonType = $(this).attr('class')
						// jika tombol edi ditekan
						if(buttonType.includes('btn-edit')){
							handleEditData($(this).attr('data-id'), listData)
						} else {
							handleDeleteData($(this).attr('data-id'))
						}
					})
				}
			})
		}, [listData])
		// mendapatkan data dari database saat pertama kali page loaded
		useEffect(() => {
			getAllDataProgramStudi()
		}, [])
		return (
			<div id="container">
				<div>
					<div className="title">
						<i className="fas fa-graduation-cap"></i>
						<div>
							<h1> Program Studi</h1>
							<p>Klik <strong>Tambah</strong> untuk menambahkan Program Studi. </p>
						</div>
					</div>
					{
						showMessageSuccess ? (
							<p className="successmessage"><i className="fa-solid fa-circle-info"></i> Program Studi berhasil dihapus.</p>
						) : false
					}
					<div className="btnarea btnarea-nopad">
						{ /* hide show button add */
							showForm == null ? (
								<button
									title="Tambah program studi"
									onClick={() => setShowForm('add')}
								>
									<i className="fas fa-plus"></i> Tambah</button>
							) : false
						}
					</div>
					{
						showForm != null ? (
							<FormInput 
								type={showForm} 
								setShowForm={setShowForm} 
								setListData={setListData} 
								refreshData={getAllDataProgramStudi}
								editedData={editedData}
							/>
						) : false
					}
					<div className="tablebox">
						<table id="listdata"></table>
					</div>
				</div>
			</div>
		)
	}
	const root = document.querySelector("#appss")
	const el = ReactDOM.createRoot(root)
	el.render(<App />)
	// form input program studi
	const FormInput = props => {
		const { setShowForm, setListData, refreshData, editedData, type } = props
		const [successMessage, setSuccessMessage] = useState(null)
		// on submit form add new program studi
		const handleSubmit = (e, type, editedData) => {
			e.preventDefault()
			const data = Object.fromEntries(new FormData(document.querySelector('#formprogramstudi')).entries())
			let url = type == 'add' ? "<?=base_url()?>index.php/ProgramStudi/create_programstudi" : "<?=base_url()?>index.php/ProgramStudi/update_programstudi"
			// menyisipkan id program studi jika edit
			if(type == 'edit'){
				data.id = editedData.id
			}
			$.ajax({
				url,
				data,
				method: 'POST',
				success: data => {
					// on success
					if(data == "true" || data > 0){
						$('#formprogramstudi')[0].reset()
						setSuccessMessage(`Program Studi berhasil di${type == 'add' ? 'tambahkan' : 'update'}, Terima kasih.`)
						refreshData()
						setTimeout(() => setSuccessMessage(null),5000)
					}
				},
				error: () => {
					alert('Gagal menyimpan data Program Studi')
				}
			})
		}
		useEffect(() => {
			if(type == 'edit'){
				// update nilai elemen berdasarkan key value
				for(let obj in editedData){
					$(`[name="${obj}"]`).val(editedData[obj])
				}
			}
		}, [type, editedData])
		return (
			<div className="forms">
				<h1>{type == 'add' ? 'Tambah' : 'Update'} Program Studi</h1>
				<p>Silahkan lengkapi form dibawah ini.</p>
				{
					successMessage != null ? (
						<span className="successmessage"><i className="fas fa-check-circle"></i> {successMessage}</span>
					) : false
				}
				<form id="formprogramstudi" onSubmit={e => handleSubmit(e, type, editedData)}>
					<div className="wrap">
						<div className="formel">
							<label htmlFor="nama">Nama</label>
							<input name="nama" type="text" placeholder="e.g. Manajemen Informatika" required />
						</div>
						<div className="formel">
							<label htmlFor="programpendidikan">Program Pendidikan</label>
							<select required name="program_pendidikan">
								<option value="">--- Pilih Program ---</option>
								<option value="Diploma III">Diploma III</option>
								<option value="Diploma IV">Diploma IV</option>
								<option value="Sarjana">Sarjana</option>
							</select>
						</div>
						<div className="formel">
							<label htmlFor="akreditasi">Akreditasi</label>
							<select required name="akreditasi">
								<option value="">--- Pilih Akreditasi ---</option>
								<option value="Baik Sekali">Baik Sekali</option>
								<option value="Baik">Baik</option>
								<option value="Unggul">Unggul</option>
							</select>
						</div>
						<div className="formel fulls">
							<label htmlFor="sk">SK Akreditasi</label>
							<input name="sk_akreditasi" type="text" placeholder="e.g. 001/TI/AKRED/UNSIA" required />
						</div>
					</div>
					<div className="btnarea">
						<a href="#" onClick={() => setShowForm(null)}>Batal</a>
						<button><i className="fas fa-save"></i> Simpan</button>
					</div>
				</form>
			</div>
		)
	}
</script>