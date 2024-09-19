<table>
    <thead>
        <tr>
            <th>ID Proposal</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Proposal</th>
            <th>Kode Penelitian</th>
            <th>ID Penilai 1</th>
            <th>ID Penilai 2</th>
            <th>ID Penilai 3</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
        <tr>
            <td>{{ $data->proposalSkripsi->id }}</td>
            <td>{{ $data->proposalSkripsi->mahasiswa->nim }}</td>
            <td>{{ $data->proposalSkripsi->mahasiswa->nama }}</td>
            <td>{{ $data->proposalSkripsi->judul_proposal }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>